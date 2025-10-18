<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CollectionController extends Controller
{
    /**
     * Return aggregate counts for the authenticated user's collections.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount(['clothes', 'outfits']);

        return response()->json([
            'success' => true,
            'message' => 'Collection counts retrieved successfully.',
            'data' => [
                'clothes_count' => $user->clothes_count,
                'outfits_count' => $user->outfits_count,
            ],
        ]);
    }
}
