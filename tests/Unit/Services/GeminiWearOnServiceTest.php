<?php

use App\Services\AI\GeminiWearOnService;
use Gemini\Enums\MimeType;

it('normalises supported mime types', function () {
    expect(GeminiWearOnService::normaliseImageMime('image/jpeg'))->toBe(MimeType::IMAGE_JPEG);
    expect(GeminiWearOnService::normaliseImageMime('image/jpg'))->toBe(MimeType::IMAGE_JPEG);
    expect(GeminiWearOnService::normaliseImageMime('image/png'))->toBe(MimeType::IMAGE_PNG);
    expect(GeminiWearOnService::normaliseImageMime('image/webp'))->toBe(MimeType::IMAGE_WEBP);
    expect(GeminiWearOnService::normaliseImageMime('image/heic'))->toBe(MimeType::IMAGE_HEIC);
    expect(GeminiWearOnService::normaliseImageMime('image/heif'))->toBe(MimeType::IMAGE_HEIF);
});

it('falls back to png for unknown mime types', function () {
    expect(GeminiWearOnService::normaliseImageMime('application/octet-stream'))
        ->toBe(MimeType::IMAGE_PNG);
    expect(GeminiWearOnService::normaliseImageMime(null))->toBe(MimeType::IMAGE_PNG);
});
