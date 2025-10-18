<?php

namespace App\Http\Controllers;

use App\Models\Outfit\Outfit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class OutfitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $query = Outfit::query()->ownedBy($user->id);

        $tag = $validated['tag'] ?? null;
        $status = $validated['status'] ?? null;

        if ($tag !== null) {
            $query->where('tag', $tag);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($tag === null && $status === null) {
            $query->where('tag', 'matches');
        }

        $outfits = $query
            ->orderByDesc('generated_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Outfits retrieved successfully.',
            'data' => [
                'outfits' => $outfits->map->toArray()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_path' => ['required', 'string'],
            'image_url' => ['required', 'url'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_path' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        try {
            $outfit = Outfit::query()->create([
                'user_id' => $user->id,
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'tag' => 'matches',
                'image_path' => $validated['image_path'],
                'thumbnail_path' => $validated['thumbnail_path'] ?? null,
                'metadata' => [
                    'source_image_url' => $validated['image_url'],
                    'ingestion_status' => 'completed',
                ],
                'generated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to store outfit', [
                'user_id' => $user->id,
                'payload' => $validated,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to store outfit at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Outfit saved successfully.',
            'data' => [
                'outfit' => $outfit->fresh()->toArray(),
            ],
        ], 201);
    }

    public function destroy(Request $request, Outfit $outfit): JsonResponse
    {
        $user = $request->user();

        if ($outfit->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to delete this outfit.',
            ], 403);
        }

        try {
            $outfit->delete();
        } catch (Throwable $exception) {
            Log::error('Failed to delete outfit', [
                'user_id' => $user->id,
                'outfit_id' => $outfit->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete outfit at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Outfit deleted successfully.',
            'data' => [
                'outfit_id' => $outfit->id,
            ],
        ]);
    }
}
