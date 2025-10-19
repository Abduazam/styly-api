<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Clothe\Clothe;
use App\Services\AI\GeminiMatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class AiGenerateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'selected_clothes' => ['required', 'array', 'min:1'],
            'selected_clothes.*' => ['required', 'integer', 'exists:clothes,id'],
            'occasion' => ['nullable', 'string'],
            'look' => ['nullable', 'string'],
        ]);

        $selectedIds = array_values($validated['selected_clothes']);
        $user = $request->user();

        $clothes = Clothe::query()
            ->ownedBy($user->id)
            ->whereIn('id', $selectedIds)
            ->get();

        if ($clothes->count() !== count($selectedIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected clothes do not belong to you.',
            ], 422);
        }

        try {
            $service = new GeminiMatcherService($user->id);
            $collage = $service->createCollage($clothes);
        } catch (Throwable $exception) {
            logger()->error('Failed to generate outfit collage', [
                'user_id' => $user->id,
                'selected_clothe_ids' => $selectedIds,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate outfit collage at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Outfit collage generated successfully.',
            'data' => [
                'clothes' => $clothes->map->toArray()->all(),
                'image_path' => $collage['image_path'],
                'image_url' => $collage['image_url'],
            ],
        ]);
    }
}
