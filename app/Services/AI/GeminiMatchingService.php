<?php

namespace App\Services\AI;

use App\Models\Clothe\Clothe;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class GeminiMatchingService
{
    protected static string $model = 'gemini-2.5-flash-image';
    protected static string $folder = 'matching/{id}';

    public function __construct(protected int $user_id)
    {
    }

    /**
     * Find matching clothes from user's wardrobe based on selected clothes.
     */
    public function findMatchingClothes(Collection $selectedClothes, Collection $availableClothes): array
    {
        if ($selectedClothes->isEmpty()) {
            return [];
        }

        $selectedClothesData = $this->prepareClothesData($selectedClothes);
        $availableClothesData = $this->prepareClothesData($availableClothes);

        $prompt = $this->buildMatchingPrompt($selectedClothesData, $availableClothesData);

        $result = Gemini::generativeModel(model: self::$model)
            ->generateContent($prompt);

        return $this->extractMatchingResults($result);
    }

    /**
     * Find matching clothes using images for visual analysis.
     */
    public function findMatchingClothesWithImages(Collection $selectedClothes, Collection $availableClothes): array
    {
        if ($selectedClothes->isEmpty()) {
            return [];
        }

        $selectedImages = $this->prepareClothesImages($selectedClothes);
        $availableImages = $this->prepareClothesImages($availableClothes);

        $prompt = $this->buildVisualMatchingPrompt($selectedImages, $availableImages);

        $parts = array_merge([$prompt], $selectedImages, $availableImages);

        $result = Gemini::generativeModel(model: self::$model)
            ->generateContent($parts);

        return $this->extractMatchingResults($result);
    }

    /**
     * Prepare clothes data for AI analysis.
     */
    protected function prepareClothesData(Collection $clothes): array
    {
        return $clothes->map(function (Clothe $clothe) {
            return [
                'id' => $clothe->id,
                'label' => $clothe->label,
                'category' => $clothe->category,
                'occasion' => $clothe->occasion,
                'season' => $clothe->season,
                'color_palette' => $clothe->color_palette,
                'ai_summary' => $clothe->ai_summary,
                'metadata' => $clothe->metadata,
            ];
        })->toArray();
    }

    /**
     * Prepare clothes images for visual analysis.
     */
    protected function prepareClothesImages(Collection $clothes): array
    {
        $images = [];

        foreach ($clothes as $clothe) {
            if ($clothe->image_path && Storage::disk('public')->exists($clothe->image_path)) {
                $imageContent = Storage::disk('public')->get($clothe->image_path);
                $images[] = new Blob(
                    mimeType: MimeType::IMAGE_JPEG,
                    data: base64_encode($imageContent)
                );
            }
        }

        return $images;
    }

    /**
     * Build prompt for text-based matching.
     */
    protected function buildMatchingPrompt(array $selectedClothes, array $availableClothes): string
    {
        $selectedJson = json_encode($selectedClothes, JSON_PRETTY_PRINT);
        $availableJson = json_encode($availableClothes, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are a fashion expert AI that finds matching clothes from a user's wardrobe.

SELECTED CLOTHES (what the user wants to match):
{$selectedJson}

AVAILABLE CLOTHES (from user's wardrobe to choose from):
{$availableJson}

Analyze the selected clothes and find the best matching items from the available clothes. Consider:
1. Color harmony and complementary colors
2. Style compatibility (casual, formal, etc.)
3. Season appropriateness
4. Occasion matching
5. Category balance (tops, bottoms, shoes, accessories)
6. Overall aesthetic coherence

Respond with a JSON object containing:
{
  "matches": [
    {
      "clothe_id": "ID of the matching item",
      "match_score": "0.0 to 1.0 confidence score",
      "reasoning": "Brief explanation of why this item matches",
      "style_notes": "Additional styling suggestions"
    }
  ],
  "outfit_suggestions": [
    {
      "name": "Outfit name",
      "items": ["clothe_id1", "clothe_id2", ...],
      "description": "Complete outfit description",
      "occasion": "suitable occasion",
      "confidence": "0.0 to 1.0"
    }
  ],
  "styling_tips": [
    "General styling advice for the selected items"
  ]
}

Return only the JSON response, no additional text.
PROMPT;
    }

    /**
     * Build prompt for visual matching using images.
     */
    protected function buildVisualMatchingPrompt(array $selectedImages, array $availableImages): string
    {
        return <<<PROMPT
You are a fashion expert AI that analyzes clothing images to find matching items.

The first set of images are SELECTED CLOTHES that the user wants to match.
The second set of images are AVAILABLE CLOTHES from the user's wardrobe.

Analyze the visual elements of the selected clothes and find the best matching items from the available clothes. Consider:
1. Color harmony and complementary colors
2. Visual style compatibility
3. Pattern coordination
4. Texture combinations
5. Overall aesthetic coherence
6. Fit and silhouette compatibility

Respond with a JSON object containing:
{
  "matches": [
    {
      "clothe_id": "ID of the matching item (you'll need to infer this from the image order)",
      "match_score": "0.0 to 1.0 confidence score",
      "reasoning": "Brief explanation of why this item matches visually",
      "style_notes": "Additional styling suggestions"
    }
  ],
  "outfit_suggestions": [
    {
      "name": "Outfit name",
      "items": ["clothe_id1", "clothe_id2", ...],
      "description": "Complete outfit description",
      "occasion": "suitable occasion",
      "confidence": "0.0 to 1.0"
    }
  ],
  "styling_tips": [
    "General styling advice for the selected items"
  ]
}

Return only the JSON response, no additional text.
PROMPT;
    }

    /**
     * Extract and normalize matching results from Gemini response.
     */
    protected function extractMatchingResults(GenerateContentResponse $response): array
    {
        foreach ($response->parts() as $part) {
            if ($part->text && is_null($part->inlineData)) {
                $payload = $this->parseGeminiJson($part->text);

                if (is_array($payload)) {
                    return $this->normalizeMatchingResults($payload);
                }
            }
        }

        return $this->defaultMatchingResults();
    }

    /**
     * Parse and clean Gemini JSON response.
     */
    protected function parseGeminiJson(string $raw): ?array
    {
        // Remove leading/trailing whitespace and triple backticks
        $cleaned = trim($raw);

        // Remove Markdown code block wrapper (```json ... ```)
        $cleaned = preg_replace('/^```json\s*/', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        // Trim any remaining whitespace
        $cleaned = trim($cleaned);

        // Handle edge case where triple quotes wrap the entire thing
        if (str_starts_with($cleaned, '"""')) {
            $cleaned = substr($cleaned, 3);
        }
        if (str_ends_with($cleaned, '"""')) {
            $cleaned = substr($cleaned, 0, -3);
        }

        $cleaned = trim($cleaned);

        // Remove the Markdown code block wrapper again if it exists
        $cleaned = preg_replace('/^```json\s*/', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $cleaned = trim($cleaned);

        // Attempt to decode JSON
        $decoded = json_decode($cleaned, true);

        // Return null if JSON is invalid
        if (json_last_error() !== JSON_ERROR_NONE) {
            logger()->error('Gemini JSON Parse Error', [
                'error' => json_last_error_msg(),
                'raw' => $raw,
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Normalize matching results to ensure consistent structure.
     */
    protected function normalizeMatchingResults(array $data): array
    {
        return [
            'matches' => array_values(array_filter((array) ($data['matches'] ?? []))),
            'outfit_suggestions' => array_values(array_filter((array) ($data['outfit_suggestions'] ?? []))),
            'styling_tips' => array_values(array_filter((array) ($data['styling_tips'] ?? []))),
        ];
    }

    /**
     * Default matching results when AI fails.
     */
    protected function defaultMatchingResults(): array
    {
        return [
            'matches' => [],
            'outfit_suggestions' => [],
            'styling_tips' => ['Unable to find matches at this time. Please try again.'],
        ];
    }
}
