<?php

namespace App\Services\AI;

use App\Contracts\Abstracts\AbstractGeminiService;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Storage;

final class GeminiWearOnService extends AbstractGeminiService
{
    protected static string $folderTemplate = 'wears/{id}';

    /**
     * Generate a photorealistic image of the user wearing the supplied outfit reference.
     *
     * @param  array<string, mixed>  $context
     * @return array{image_path: string, image_url: string, prompt: string}
     */
    public function createWearOnImage(
        string $userImageBinary,
        MimeType $userImageMime,
        string $outfitImageBinary,
        MimeType $outfitImageMime,
        array $context = []
    ): array {
        $prompt = $this->buildPrompt();

        $response = Gemini::generativeModel(model: $this->getModel())
            ->generateContent([
                $prompt,
                $this->toBlob($userImageBinary, $userImageMime),
                $this->toBlob($outfitImageBinary, $outfitImageMime),
            ]);

        $inlineBlob = $this->inlineBlobOrFail($response);
        $imageBinary = $this->decodeBase64Image($inlineBlob->data);

        $mimeType = $inlineBlob->mimeType?->value ?? MimeType::IMAGE_PNG->value;
        $extension = $this->resolveExtensionFromMime($mimeType, 'png');
        $path = $this->storePublicImage($imageBinary, $extension, 'Failed to store Gemini wear-on result.');

        return [
            'image_path' => $path,
            'image_url' => Storage::disk('public')->url($path),
            'prompt' => $prompt,
        ];
    }

    protected function toBlob(string $binary, MimeType $mimeType): Blob
    {
        return new Blob(
            mimeType: $mimeType,
            data: base64_encode($binary)
        );
    }

    public static function normaliseImageMime(?string $value): MimeType
    {
        return match (strtolower((string) $value)) {
            'image/jpeg', 'image/jpg' => MimeType::IMAGE_JPEG,
            'image/png' => MimeType::IMAGE_PNG,
            'image/webp' => MimeType::IMAGE_WEBP,
            'image/heic' => MimeType::IMAGE_HEIC,
            'image/heif' => MimeType::IMAGE_HEIF,
            default => MimeType::IMAGE_PNG,
        };
    }

    private function buildPrompt(): string
    {
        $promptArray = [
            "instructions" => [
                "primary_task" => "Create a virtual try-on image by combining the user's facial features with the provided outfit. The goal is to show what the user looks like wearing the outfit.",
                "strict_requirements" => [
                    "PRESERVE EXACTLY the user's face - facial structure, bone structure, jawline, cheekbones, chin shape, and facial proportions must remain identical",
                    "PRESERVE the user's natural facial features - eyes, nose, mouth, skin texture must not be altered or changed",
                    "Do NOT modify, enhance, or change the user's face in any way - no smoothing, no reshaping, no beautification",
                    "Do NOT change the user's face shape, size, or appearance - keep it exactly as shown in the reference image",
                    "Apply the outfit clothing to a neutral body that matches the user's general build",
                    "Use natural, realistic proportions for the body",
                    "Ensure the outfit fits appropriately on the body without appearing too tight or too loose",
                    "Match lighting, shadows, and color grading between the face and outfit",
                    "Use a clean, neutral background (white or light gray)",
                    "Position the person in a neutral, forward-facing or slight three-quarter pose to showcase the outfit",
                    "This is NOT a photo editing or face enhancement service - preserve the user's original facial appearance exactly as provided"
                ],
                "what_to_preserve" => [
                    "from_user_image" => "The exact facial features, face shape, facial structure, skin tone, complexion, and all distinguishing characteristics of the user's face",
                    "from_outfit_image" => "The clothing design, style, color, texture, pattern, and fit of the garment"
                ],
                "what_to_generate" => "A realistic composite image showing the user's face (unchanged) on a naturally proportioned body wearing the provided outfit",
                "output_style" => "Professional, clean, suitable for e-commerce or virtual fitting room",
                "important_notes" => "This is a virtual try-on service. The goal is to show what the user looks like wearing the outfit, NOT to create an idealized or altered version of the user"
            ]
        ];

        return json_encode($promptArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
