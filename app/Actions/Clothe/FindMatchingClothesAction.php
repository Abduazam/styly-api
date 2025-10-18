<?php

namespace App\Actions\Clothe;

use App\Models\Clothe\Clothe;
use App\Queries\Clothe\ClotheFindByUserQuery;
use App\Services\AI\GeminiMatchingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

final class FindMatchingClothesAction
{
    protected GeminiMatchingService $matchingService;
    protected ClotheFindByUserQuery $clotheQuery;

    public function __construct(
        protected int $userId,
        protected array $selectedClotheIds,
        protected bool $useVisualAnalysis = false
    ) {
        $this->matchingService = new GeminiMatchingService($this->userId);
        $this->clotheQuery = new ClotheFindByUserQuery();
    }

    public function run(): array
    {
        try {
            // Get selected clothes
            $selectedClothes = $this->clotheQuery->queryByIds($this->userId, $this->selectedClotheIds);
            
            if ($selectedClothes->isEmpty()) {
                return $this->createEmptyResponse('No selected clothes found.');
            }

            // Get available clothes (excluding selected ones)
            $availableClothes = $this->clotheQuery->query($this->userId, $this->selectedClotheIds);
            
            if ($availableClothes->isEmpty()) {
                return $this->createEmptyResponse('No other clothes available for matching.');
            }

            // Find matches using AI
            if ($this->useVisualAnalysis) {
                $results = $this->matchingService->findMatchingClothesWithImages($selectedClothes, $availableClothes);
            } else {
                $results = $this->matchingService->findMatchingClothes($selectedClothes, $availableClothes);
            }

            // Enhance results with full clothe data
            $enhancedResults = $this->enhanceResultsWithClotheData($results, $availableClothes);

            return [
                'success' => true,
                'selected_clothes' => $selectedClothes->map(fn(Clothe $clothe) => $clothe->toArray())->toArray(),
                'matching_results' => $enhancedResults,
                'total_available' => $availableClothes->count(),
                'analysis_type' => $this->useVisualAnalysis ? 'visual' : 'textual',
            ];

        } catch (\Exception $e) {
            Log::error('Error finding matching clothes', [
                'user_id' => $this->userId,
                'selected_clothe_ids' => $this->selectedClotheIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Unable to find matching clothes at this time.',
                'selected_clothes' => [],
                'matching_results' => $this->createEmptyResponse('Error occurred during analysis.'),
                'total_available' => 0,
                'analysis_type' => $this->useVisualAnalysis ? 'visual' : 'textual',
            ];
        }
    }

    /**
     * Enhance AI results with full clothe data.
     */
    protected function enhanceResultsWithClotheData(array $results, Collection $availableClothes): array
    {
        $clothesById = $availableClothes->keyBy('id');

        // Enhance matches with full clothe data
        if (isset($results['matches'])) {
            $results['matches'] = array_map(function ($match) use ($clothesById) {
                if (isset($match['clothe_id']) && $clothesById->has($match['clothe_id'])) {
                    $clothe = $clothesById->get($match['clothe_id']);
                    $match['clothe'] = $clothe->toArray();
                }
                return $match;
            }, $results['matches']);
        }

        // Enhance outfit suggestions with full clothe data
        if (isset($results['outfit_suggestions'])) {
            $results['outfit_suggestions'] = array_map(function ($outfit) use ($clothesById) {
                if (isset($outfit['items']) && is_array($outfit['items'])) {
                    $outfit['clothes'] = [];
                    foreach ($outfit['items'] as $clotheId) {
                        if ($clothesById->has($clotheId)) {
                            $outfit['clothes'][] = $clothesById->get($clotheId)->toArray();
                        }
                    }
                }
                return $outfit;
            }, $results['outfit_suggestions']);
        }

        return $results;
    }

    /**
     * Create empty response structure.
     */
    protected function createEmptyResponse(string $message): array
    {
        return [
            'matches' => [],
            'outfit_suggestions' => [],
            'styling_tips' => [$message],
        ];
    }
}
