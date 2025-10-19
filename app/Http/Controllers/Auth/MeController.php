<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    /**
     * Return the authenticated user profile snapshot.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount(['clothes', 'outfits']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'clothes_count' => $user->clothes_count,
                'outfits_count' => $user->outfits_count,
            ],
        ]);
    }
}
