<?php

namespace App\Services\AI;

use App\Contracts\Abstracts\AbstractGeminiService;
use App\Models\Clothe\Clothe;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class GeminiMatcherService extends AbstractGeminiService
{
    protected static string $folderTemplate = 'matching/{id}';

    /**
     * Generate a flat lay college for the provided clothes.
     *
     * @return array{image_path: string, image_url: string}
     */
    public function createCollage(Collection $clothes): array
    {
        if ($clothes->isEmpty()) {
            throw new RuntimeException('No clothes supplied for collage generation.');
        }

        $imageBlobs = $this->resolveClotheBlobs($clothes);

        if (empty($imageBlobs)) {
            throw new RuntimeException('Selected clothes do not have stored images.');
        }

        $prompt = <<<'PROMPT'
You are a fashion stylist assistant. Combine the provided garment cut-outs into a single image.
Create a high-quality flat lay collage of a complete outfit on a clean white background. Arrange the items neatly so they feel cohesive and balanced, maintaining natural proportions and subtle shadows. Return only the composed outfit image as inline data.
PROMPT;

        $parts = array_merge([$prompt], $imageBlobs);

        $response = Gemini::generativeModel(model: $this->getModel())
            ->generateContent($parts);

        $inlineBlob = $this->inlineBlobOrFail($response);
        $imageBinary = $this->decodeBase64Image($inlineBlob->data);

        $mimeType = $inlineBlob->mimeType?->value ?? MimeType::IMAGE_PNG->value;
        $extension = $this->resolveExtensionFromMime($mimeType, 'png');
        $path = $this->storePublicImage($imageBinary, $extension, 'Failed to store Gemini collage to disk.');

        return [
            'image_path' => $path,
            'image_url' => Storage::disk('public')->url($path),
        ];
    }

    /**
     * @return list<Blob>
     */
    protected function resolveClotheBlobs(Collection $clothes): array
    {
        return $clothes
            ->map(function (Clothe $clothe) {
                $binary = $this->resolveClotheImageBinary($clothe);

                if ($binary === null) {
                    return null;
                }

                return new Blob(
                    mimeType: $this->determineMimeType($clothe),
                    data: base64_encode($binary)
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function resolveClotheImageBinary(Clothe $clothe): ?string
    {
        $paths = array_filter([
            $clothe->image_path,
            $clothe->thumbnail_path,
            $clothe->source_path,
        ]);

        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->get($path);
            }
        }

        return null;
    }

    protected function determineMimeType(Clothe $clothe): MimeType
    {
        $path = $clothe->image_path ?? $clothe->thumbnail_path ?? $clothe->source_path;

        if ($path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            return match ($extension) {
                'jpg', 'jpeg' => MimeType::IMAGE_JPEG,
                'png' => MimeType::IMAGE_PNG,
                'webp' => MimeType::IMAGE_WEBP,
                default => MimeType::IMAGE_PNG,
            };
        }

        return MimeType::IMAGE_PNG;
    }

}
