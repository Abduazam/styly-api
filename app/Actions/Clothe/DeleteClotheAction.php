<?php

namespace App\Actions\Clothe;

use App\Models\Clothe\Clothe;

final class DeleteClotheAction
{
    public function __construct(protected Clothe $clothe) { }

    public function run(): ?bool
    {
        return $this->clothe->delete();
    }
}
