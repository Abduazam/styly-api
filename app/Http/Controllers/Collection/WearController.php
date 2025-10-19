<?php

namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use App\Models\Outfit\Outfit;
use App\Models\Wear\Wear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WearController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        //
    }

    public function store(Request $request): JsonResponse
    {
        //
    }

    public function destroy(Request $request, Wear $wear): JsonResponse
    {
        $user = $request->user();

        if ($wear->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to delete this wear.',
            ], 403);
        }

        try {
            $wear->delete();
        } catch (Throwable $exception) {
            Log::error('Failed to delete wear', [
                'user_id' => $user->id,
                'wear_id' => $wear->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete wear at this time.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Outfit deleted successfully.',
            'data' => [
                'wear_id' => $wear->id,
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $wear = Outfit::query()->where('id', '=', $id)->first();

        if ($wear) {
            return response()->json([
                'success' => true,
                'message' => 'Outfit retrieved successfully.',
                'data' => $wear->toArray(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Outfit not found.',
            ]);
        }
    }
}
