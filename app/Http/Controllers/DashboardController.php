<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    /**
     * Return aggregate counts for the authenticated user's collections.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->loadCount(['clothes', 'outfits', 'wears']);

        $wardrobeItems = $user->clothes()
            ->latest('created_at')
            ->limit(4)
            ->get();

        $outfitItems = $user->outfits()
            ->latest('created_at')
            ->limit(4)
            ->get();

        $wearItems = $user->wears()
            ->latest('created_at')
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Collection overview fetched.',
            'data' => [
                'wardrobe' => [
                    'count' => $user->clothes_count,
                    'items' => $wardrobeItems,
                ],
                'outfits' => [
                    'count' => $user->outfits_count,
                    'items' => $outfitItems,
                ],
                'wears' => [
                    'count' => $user->wears_count,
                    'items' => $wearItems,
                ],
            ],
        ]);
    }
}
