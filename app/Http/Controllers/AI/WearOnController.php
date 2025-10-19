<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Outfit\Outfit;
use App\Models\Wear\Wear;
use App\Services\AI\GeminiWearOnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

final class WearOnController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outfit_id' => ['nullable', 'integer', 'exists:outfits,id'],
            'image_path' => ['nullable', 'string'],
            'user_image' => ['required', 'image', 'max:8192'], // 8MB max upload
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if (empty($validated['outfit_id']) && empty($validated['image_path'])) {
            throw ValidationException::withMessages([
                'outfit_id' => 'Provide either an outfit_id or an image_path for the outfit reference.',
                'image_path' => 'Provide either an outfit_id or an image_path for the outfit reference.',
            ]);
        }

        $user = $request->user();
        $userImage = $request->file('user_image');

        if (! $user || ! $userImage instanceof UploadedFile) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to process wear-on request without an authenticated user and face image.',
            ], 401);
        }

        $service = new GeminiWearOnService($user->id);

        try {
            $userBinary = $userImage->getContent();
            $userMime = GeminiWearOnService::normaliseImageMime($userImage->getMimeType());

            $outfitSource = $this->resolveOutfitSource(
                $validated['outfit_id'] ?? null,
                $validated['image_path'] ?? null,
                $user->id
            );

            $context = $this->buildContextPayload($outfitSource, $validated, $userImage);

            $result = $service->createWearOnImage(
                $userBinary,
                $userMime,
                $outfitSource['binary'],
                $outfitSource['mime'],
                $context
            );

            $wear = Wear::query()->create([
                'user_id' => $user->id,
                'title' => $validated['title']
                    ?? $outfitSource['title']
                    ?? 'Wear On Look',
                'description' => $validated['description']
                    ?? $outfitSource['description']
                    ?? null,
                'generated_image_url' => $result['image_url'],
                'metadata' => $this->buildWearMetadata($result, $outfitSource, $validated),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Wear-on generation failed', [
                'user_id' => $user->id,
                'outfit_id' => $validated['outfit_id'] ?? null,
                'image_path' => $validated['image_path'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate wear-on preview at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wear-on preview generated successfully.',
            'data' => [
                'wear' => $wear->toArray(),
                'image_path' => $result['image_path'],
                'image_url' => $result['image_url'],
            ],
        ], 201);
    }

    /**
     * @return array{binary: string, mime: \Gemini\Enums\MimeType, title?: string|null, description?: string|null, source: string, source_reference: mixed, outfit?: array<string, mixed>, storage_path?: string|null}
     */
    protected function resolveOutfitSource(?int $outfitId, ?string $imagePath, int $userId): array
    {
        if ($outfitId) {
            $outfit = Outfit::query()
                ->ownedBy($userId)
                ->find($outfitId);

            if (! $outfit) {
                throw ValidationException::withMessages([
                    'outfit_id' => 'The selected outfit does not belong to you or does not exist.',
                ]);
            }

            $path = $outfit->image_path ?? $outfit->thumbnail_path;

            if (! $path) {
                throw ValidationException::withMessages([
                    'outfit_id' => 'The selected outfit does not have an image to reference.',
                ]);
            }

            if (! Storage::disk('public')->exists($path)) {
                throw ValidationException::withMessages([
                    'outfit_id' => 'Unable to locate the outfit image on disk.',
                ]);
            }

            $binary = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path) ?? 'image/png';

            return [
                'binary' => $binary,
                'mime' => GeminiWearOnService::normaliseImageMime($mime),
                'title' => $outfit->title,
                'description' => $outfit->description,
                'source' => 'outfit',
                'source_reference' => $outfit->id,
                'storage_path' => $path,
                'outfit' => $outfit->only(['id', 'uuid', 'title', 'description', 'tag', 'status', 'image_path', 'thumbnail_path']),
            ];
        }

        $imagePath = $imagePath ? trim($imagePath) : null;

        if ($imagePath) {
            if (! Storage::disk('public')->exists($imagePath)) {
                throw ValidationException::withMessages([
                    'image_path' => 'Unable to locate the outfit image on disk.',
                ]);
            }

            $binary = Storage::disk('public')->get($imagePath);
            $mime = Storage::disk('public')->mimeType($imagePath) ?? 'image/png';

            return [
                'binary' => $binary,
                'mime' => GeminiWearOnService::normaliseImageMime($mime),
                'title' => null,
                'description' => null,
                'source' => 'image_path',
                'source_reference' => $imagePath,
                'storage_path' => $imagePath,
            ];
        }

        throw ValidationException::withMessages([
            'outfit_id' => 'A valid outfit reference is required.',
            'image_path' => 'A valid outfit reference is required.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $outfitSource
     * @param  array<string, mixed>  $validated
     */
    protected function buildWearMetadata(array $result, array $outfitSource, array $validated): array
    {
        return [
            'source' => $outfitSource['source'],
            'source_reference' => $outfitSource['source_reference'],
            'storage_path' => $outfitSource['storage_path'] ?? null,
            'outfit' => $outfitSource['outfit'] ?? null,
            'image_path' => $result['image_path'],
            'prompt' => $result['prompt'],
            'request' => [
                'outfit_id' => $validated['outfit_id'] ?? null,
                'image_path' => $validated['image_path'] ?? null,
                'title' => $validated['title'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $outfitSource
     * @param  array<string, mixed>  $validated
     */
    protected function buildContextPayload(array $outfitSource, array $validated, UploadedFile $userImage): array
    {
        $context = [
            'viewer' => [
                'uploaded_filename' => $userImage->getClientOriginalName(),
            ],
            'outfit_source' => [
                'type' => $outfitSource['source'],
                'reference' => $outfitSource['source_reference'],
            ],
        ];

        if (! empty($validated['title'])) {
            $context['desired_title'] = $validated['title'];
        }

        if (! empty($validated['description'])) {
            $context['desired_description'] = $validated['description'];
        }

        if (! empty($outfitSource['outfit'])) {
            $context['outfit_snapshot'] = $outfitSource['outfit'];
        }

        if (! empty($outfitSource['storage_path'])) {
            $context['outfit_source']['storage_path'] = $outfitSource['storage_path'];
        }

        return $context;
    }
}
