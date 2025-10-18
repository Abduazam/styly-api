<?php

namespace App\Services\AI;

use App\Models\Clothe\Clothe;
use Gemini\Data\Blob;
use Gemini\Data\Part;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

final class GeminiWardrobeService
{
    protected static string $model = 'gemini-2.5-flash-image';
    protected static string $folder = 'wardrobe/{id}';

    public function __construct(protected int $user_id)
    {

    }

    /**
     * Remove the background from an uploaded wardrobe image using Gemini image editing.
     */
    public function processImage(UploadedFile $file): array
    {
        $prompt = <<<'PROMPT'
Remove the current background and put the product on a clean white surface with a soft shadow.
Respond with two parts:
1. A JSON object describing the garment with the schema:
{
  "label": "concise marketing name",
  "category": "top | bottom | shoes | outerwear | accessory",
  "occasion": "casual | business | formal | party | athleisure | lounge | null",
  "season": "spring | summer | autumn | winter | all-season | null",
  "color_palette": {"primary": "#RRGGBB", "secondary": "#RRGGBB"},
  "ai_summary": {
    "description": "1-2 sentence consumer friendly summary",
    "materials": ["fabric or composition"]
  },
  "metadata": {
    "fit": "slim | regular | oversized | relaxed | tailored | null",
    "pattern": "solid | striped | plaid | graphic | textured | null",
    "care": "care recommendation or null"
  }
}
Ensure color codes are valid hex strings and omit unavailable values using null.
2. The edited product image on a clean white background with a soft shadow as inline binary data.
PROMPT;

        $result = Gemini::generativeModel(model: self::$model)
            ->generateContent([
                $prompt,
                new Blob(
                    mimeType: $this->resolveImageMimeType($file),
                    data: base64_encode($file->getContent())
                )
            ]);

        $structured = $this->extractStructuredData($result);
        $inlineBlob = $this->extractInlineBlob($result);

        $imageBinary = base64_decode($inlineBlob->data, true);

        if ($imageBinary === false) {
            throw new RuntimeException('Gemini returned invalid image data.');
        }

        $path = $this->buildStoragePath($inlineBlob->mimeType->value);

        if (! Storage::disk('public')->put($path, $imageBinary)) {
            throw new RuntimeException('Failed to store Gemini result to the public disk.');
        }

        $structured['image_path'] = $path;

        return $structured;
    }

    protected function extractInlineBlob(GenerateContentResponse $response): Blob
    {
        foreach ($response->candidates as $candidate) {
            foreach ($candidate->content->parts as $part) {
                if ($part->inlineData !== null) {
                    return $part->inlineData;
                }
            }
        }

        throw new RuntimeException('Gemini response does not contain inline image data.');
    }

    protected function extractStructuredData(GenerateContentResponse $response): array
    {
        foreach ($response->parts() as $part) {
            if ($part->text && is_null($part->inlineData)) {
                $payload = $this->parseGeminiJson($part->text);

                if (is_array($payload)) {
                    return $this->normaliseStructuredData($payload);
                }
            }
        }

        return $this->defaultStructuredData();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function normaliseStructuredData(array $data): array
    {
        $metadata = (array) ($data['metadata'] ?? []);
        $metadata['ingestion_status'] = $metadata['ingestion_status'] ?? 'completed';

        $summary = (array) ($data['ai_summary'] ?? []);
        $summary['description'] = (string) ($summary['description'] ?? '');
        $summary['materials'] = array_values(array_filter((array) ($summary['materials'] ?? [])));

        $palette = (array) ($data['color_palette'] ?? []);
        $palette = array_filter([
            'primary' => $palette['primary'] ?? null,
            'secondary' => $palette['secondary'] ?? null,
        ]);

        return [
            'label' => (string) ($data['label'] ?? 'Untitled garment'),
            'category' => $data['category'] ?? null,
            'occasion' => $data['occasion'] ?? null,
            'season' => $data['season'] ?? null,
            'color_palette' => $palette ?: null,
            'ai_summary' => $summary ?: null,
            'metadata' => $metadata,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
        ];
    }

    /**
     * Attempt to decode Gemini's JSON payload and repair common formatting issues.
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

    protected function defaultStructuredData(): array
    {
        return [
            'label' => 'Untitled garment',
            'category' => null,
            'occasion' => null,
            'season' => null,
            'color_palette' => null,
            'ai_summary' => null,
            'metadata' => ['ingestion_status' => 'completed'],
            'thumbnail_path' => null,
        ];
    }

    protected function buildStoragePath(string $mimeType): string
    {
        $extension = match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => Str::after($mimeType, '/'),
        };

        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if ($extension === '' || str_contains($extension, '/')) {
            $extension = 'bin';
        }

        $path = str_replace('{id}', $this->user_id, self::$folder);

        return sprintf($path . '/%s.%s', $this->getImageID(), $extension);
    }

    private function getImageID(): string
    {
        $max = Clothe::query()->where(['user_id' => $this->user_id])->max('id');

        return $max ? $max + 1 : 1;
    }

    private function resolveImageMimeType(UploadedFile $file): MimeType
    {
        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => MimeType::IMAGE_JPEG,
            'image/png' => MimeType::IMAGE_PNG,
            'image/webp' => MimeType::IMAGE_WEBP,
            'image/heic' => MimeType::IMAGE_HEIC,
            'image/heif' => MimeType::IMAGE_HEIF,
        };
    }
}
