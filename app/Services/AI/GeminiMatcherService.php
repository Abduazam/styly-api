<?php

namespace App\Services\AI;

use App\Models\Clothe\Clothe;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class GeminiMatcherService
{
    protected static string $model = 'gemini-2.5-flash-image';
    protected static string $folder = 'matching/{id}';

    public function __construct(protected int $userId)
    {
    }

    /**
     * Generate a flat lay collage for the provided clothes.
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

        $response = Gemini::generativeModel(model: self::$model)
            ->generateContent($parts);

        $inlineBlob = $this->extractInlineBlob($response);
        $imageBinary = base64_decode($inlineBlob->data, true);

        if ($imageBinary === false) {
            throw new RuntimeException('Gemini returned invalid image data.');
        }

        $mimeType = $inlineBlob->mimeType?->value ?? MimeType::IMAGE_PNG->value;
        $path = $this->storeImage($imageBinary, $mimeType);

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

    protected function extractInlineBlob(GenerateContentResponse $response): Blob
    {
        foreach ($response->parts() as $part) {
            if ($part->inlineData) {
                return $part->inlineData;
            }
        }

        throw new RuntimeException('Gemini response does not contain inline image data.');
    }

    protected function storeImage(string $binary, string $mimeType): string
    {
        $extension = $this->resolveExtensionFromMime($mimeType);
        $folder = str_replace('{id}', (string) $this->userId, self::$folder);

        $path = sprintf($folder . '/%s.%s', Str::uuid()->toString(), $extension);

        if (! Storage::disk('public')->put($path, $binary)) {
            throw new RuntimeException('Failed to store Gemini collage to disk.');
        }

        return $path;
    }

    protected function resolveExtensionFromMime(string $mimeType): string
    {
        return match ($mimeType) {
            MimeType::IMAGE_JPEG->value => 'jpg',
            MimeType::IMAGE_WEBP->value => 'webp',
            default => 'png',
        };
    }
}
