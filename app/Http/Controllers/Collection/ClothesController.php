<?php

namespace App\Http\Controllers\Collection;

use App\Actions\Clothe\CreateClotheAction;
use App\Actions\Clothe\DeleteClotheAction;
use App\Http\Controllers\Controller;
use App\Models\Clothe\Clothe;
use App\Queries\Clothe\ClotheFindByIDQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClothesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 12);
        $perPage = max(1, min($perPage, 50));

        $clothes = Clothe::query()
            ->ownedBy($request->user()->id)
            ->latest('created_at')
            ->paginate($perPage);

        $data = collect($clothes->items())
            ->map(fn (Clothe $clothe) => $clothe->toArray())
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Clothes retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $clothes->currentPage(),
                'per_page' => $clothes->perPage(),
                'total' => $clothes->total(),
                'last_page' => $clothes->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $image = $request->file('image');

        $action = new CreateClotheAction($request->user(), $image);
        $clothe = $action->run();

        return response()->json([
            'success' => true,
            'message' => 'Clothe stored successfully',
            'data' => $clothe->toArray(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, ClotheFindByIDQuery $query): JsonResponse
    {
        $clothe = $query->query($id);

        return response()->json([
            'success' => true,
            'message' => 'Clothe retrieved successfully',
            'data' => $clothe->toArray(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, ClotheFindByIDQuery $query): JsonResponse
    {
        $action = new DeleteClotheAction($query->query($id));

        $result = $action->run();

        return response()->json([
            'success' => (bool) $result,
            'message' => $result ? 'Clothe deleted successfully' : "Couldn't delete clothe",
        ]);
    }
}
