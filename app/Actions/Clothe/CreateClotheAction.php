<?php

namespace App\Actions\Clothe;

use App\Models\Clothe\Clothe;
use App\Models\User\User;
use App\Services\AI\GeminiWardrobeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class CreateClotheAction
{
    protected GeminiWardrobeService $geminiService;

    public function __construct(
        protected User         $user,
        protected UploadedFile $file,
    ) {
        $this->geminiService = new GeminiWardrobeService($this->user->id);
    }

    public function run(): Clothe
    {
        $sourcePath = $this->storeSourceImage($this->file);

        $result = $this->geminiService->processImage($this->file);

        return Clothe::query()->create(array_merge($result, [
            'user_id' => $this->user->id,
            'source_path' => $sourcePath,
        ]));
    }

    protected function storeSourceImage(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = sprintf('%s.%s', Str::uuid(), $extension);

        return $file->storePubliclyAs("source/{$this->user->id}", $filename, 'public');
    }
}
