<?php

namespace App\Models\Brand\Traits;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRelations
{
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id', 'id');
    }
}
