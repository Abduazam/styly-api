<?php

namespace App\Models\User\Traits;

use App\Models\Clothe\Clothe;
use App\Models\Outfit\Outfit;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRelations
{
    public function clothes(): HasMany
    {
        return $this->hasMany(Clothe::class, 'user_id', 'id');
    }

    public function outfits(): HasMany
    {
        return $this->hasMany(Outfit::class, 'user_id', 'id');
    }
}
