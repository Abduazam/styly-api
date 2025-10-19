<?php

namespace App\Contracts\Abstracts;

use Gemini\Data\Blob;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

abstract class AbstractGeminiService
{
    protected readonly int $userId;

    protected static string $model = 'gemini-2.5-flash-image';

    protected static string $folderTemplate = '';

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getModel(): string
    {
        return static::$model;
    }

    protected function interpolatedFolder(): string
    {
        $folder = str_replace('{id}', (string) $this->userId, static::$folderTemplate);

        return trim($folder, '/');
    }

    protected function buildPath(string $extension): string
    {
        $folder = $this->interpolatedFolder();
        $filename = $this->generateFilename($extension);

        return $folder === ''
            ? $filename
            : sprintf('%s/%s', $folder, $filename);
    }

    protected function generateFilename(string $extension): string
    {
        return sprintf('%s.%s', Str::ulid()->toBase32(), $extension);
    }

    protected function storePublicImage(string $binary, string $extension, string $errorMessage): string
    {
        $path = $this->buildPath($extension);

        $this->writeToPublicDisk($path, $binary, $errorMessage);

        return $path;
    }

    protected function writeToPublicDisk(string $path, string $binary, string $errorMessage): void
    {
        if (! Storage::disk('public')->put($path, $binary)) {
            throw new RuntimeException($errorMessage);
        }
    }

    protected function inlineBlobOrFail(GenerateContentResponse $response): Blob
    {
        foreach ($response->parts() as $part) {
            if ($part->inlineData !== null) {
                return $part->inlineData;
            }
        }

        throw new RuntimeException('Gemini response does not contain inline image data.');
    }

    protected function decodeBase64Image(string $encoded, string $errorMessage = 'Gemini returned invalid image data.'): string
    {
        $binary = base64_decode($encoded, true);

        if ($binary === false) {
            throw new RuntimeException($errorMessage);
        }

        return $binary;
    }

    protected function resolveExtensionFromMime(string $mimeType, string $fallback = 'bin'): string
    {
        $extension = match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => Str::after($mimeType, '/'),
        };

        if ($extension === '' || str_contains($extension, '/')) {
            return $fallback;
        }

        return $extension;
    }
}
