<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\Brand\Brand;
use App\Models\Product\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $brands = Brand::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->active())
            ->with([
                'products' => fn ($query) => $query
                    ->active()
                    ->latest('created_at')
                    ->limit(12),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'slug' => $brand->slug,
                'name' => $brand->name,
                'description' => $brand->description,
                'website_url' => $brand->website_url,
                'logo_url' => $brand->logo_url,
                'metadata' => $brand->metadata,
                'products' => $brand->products->map(fn (Product $product) => [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'category' => $product->category,
                    'gender' => $product->gender,
                    'description' => $product->description,
                    'color_palette' => $product->color_palette,
                    'price' => $product->price,
                    'currency' => $product->currency,
                    'product_url' => $product->product_url,
                    'marketplace' => $product->marketplace,
                    'image_url' => $product->image_url,
                    'metadata' => $product->metadata,
                ])->values(),
            ])->values();

        return response()->json([
            'success' => true,
            'message' => 'Marketplace catalogue fetched.',
            'data' => [
                'brands' => $brands,
            ],
        ]);
    }
}
