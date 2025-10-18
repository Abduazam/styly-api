<?php

namespace App\Queries\Clothe;

use App\Models\Clothe\Clothe;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ClotheFindByIDQuery
{
    public function query(int $id): Clothe
    {
        $clothe = Clothe::query()
            ->where('id', '=', $id)
            ->first();

        if ($clothe) {
            return $clothe;
        }

        throw new ModelNotFoundException('Clothe not found');
    }
}
