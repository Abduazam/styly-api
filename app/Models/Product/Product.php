<?php

namespace App\Models\Product;

use App\Models\Product\Traits\HasRelations;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(ProductFactory::class)]
final class Product extends Model
{
    use HasFactory;
    use HasRelations;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'brand_id',
        'slug',
        'name',
        'category',
        'gender',
        'description',
        'color_palette',
        'price',
        'currency',
        'product_url',
        'marketplace',
        'image_url',
        'metadata',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'color_palette' => 'array',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'bool',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMarketplace(Builder $query, string $marketplace): Builder
    {
        return $query->where('marketplace', $marketplace);
    }
}
