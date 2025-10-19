<?php

namespace App\Models\Product\Traits;

use App\Models\Brand\Brand;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRelations
{
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
