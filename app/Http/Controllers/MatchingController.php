<?php

namespace App\Http\Controllers;

use App\Actions\Clothe\FindMatchingClothesAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MatchingController extends Controller
{
    /**
     * Find matching clothes based on selected items.
     */
    public function findMatches(Request $request): JsonResponse
    {
        $request->validate([
            'selected_clothes' => ['required', 'array', 'min:1'],
            'selected_clothes.*' => ['required', 'integer', 'exists:clothes,id'],
            'use_visual_analysis' => ['boolean'],
        ]);

        $selectedClotheIds = $request->input('selected_clothes');
        $useVisualAnalysis = $request->boolean('use_visual_analysis', false);

        // Verify all selected clothes belong to the authenticated user
        $userClothes = $request->user()->clothes()->whereIn('id', $selectedClotheIds)->get();
        
        if ($userClothes->count() !== count($selectedClotheIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected clothes do not belong to you.',
            ], 422);
        }

        $action = new FindMatchingClothesAction(
            $request->user()->id,
            $selectedClotheIds,
            $useVisualAnalysis
        );

        $result = $action->run();

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] 
                ? 'Matching clothes found successfully' 
                : ($result['error'] ?? 'Failed to find matching clothes'),
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Get outfit suggestions based on selected clothes.
     */
    public function getOutfitSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'selected_clothes' => ['required', 'array', 'min:1'],
            'selected_clothes.*' => ['required', 'integer', 'exists:clothes,id'],
            'occasion' => ['string', 'nullable'],
            'season' => ['string', 'nullable'],
        ]);

        $selectedClotheIds = $request->input('selected_clothes');
        $occasion = $request->input('occasion');
        $season = $request->input('season');

        // Verify all selected clothes belong to the authenticated user
        $userClothes = $request->user()->clothes()->whereIn('id', $selectedClotheIds)->get();
        
        if ($userClothes->count() !== count($selectedClotheIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected clothes do not belong to you.',
            ], 422);
        }

        $action = new FindMatchingClothesAction(
            $request->user()->id,
            $selectedClotheIds,
            true // Use visual analysis for outfit suggestions
        );

        $result = $action->run();

        // Filter outfit suggestions by occasion and season if provided
        if (isset($result['matching_results']['outfit_suggestions'])) {
            $result['matching_results']['outfit_suggestions'] = array_filter(
                $result['matching_results']['outfit_suggestions'],
                function ($outfit) use ($occasion, $season) {
                    $matchesOccasion = !$occasion || 
                        (isset($outfit['occasion']) && $outfit['occasion'] === $occasion);
                    $matchesSeason = !$season || 
                        (isset($outfit['season']) && $outfit['season'] === $season);
                    return $matchesOccasion && $matchesSeason;
                }
            );
        }

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] 
                ? 'Outfit suggestions generated successfully' 
                : ($result['error'] ?? 'Failed to generate outfit suggestions'),
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Get styling tips for selected clothes.
     */
    public function getStylingTips(Request $request): JsonResponse
    {
        $request->validate([
            'selected_clothes' => ['required', 'array', 'min:1'],
            'selected_clothes.*' => ['required', 'integer', 'exists:clothes,id'],
        ]);

        $selectedClotheIds = $request->input('selected_clothes');

        // Verify all selected clothes belong to the authenticated user
        $userClothes = $request->user()->clothes()->whereIn('id', $selectedClotheIds)->get();
        
        if ($userClothes->count() !== count($selectedClotheIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected clothes do not belong to you.',
            ], 422);
        }

        $action = new FindMatchingClothesAction(
            $request->user()->id,
            $selectedClotheIds,
            false // Use textual analysis for styling tips
        );

        $result = $action->run();

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] 
                ? 'Styling tips generated successfully' 
                : ($result['error'] ?? 'Failed to generate styling tips'),
            'data' => [
                'selected_clothes' => $result['selected_clothes'] ?? [],
                'styling_tips' => $result['matching_results']['styling_tips'] ?? [],
            ],
        ], $statusCode);
    }
}
