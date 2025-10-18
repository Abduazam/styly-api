<?php

namespace Database\Seeders;

use App\Models\Clothe\Clothe;
use App\Models\User\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class ClothesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first();

        $clothes = [];

        foreach ($clothes as $clothe) {
            Clothe::query()->create(array_merge($clothe, ['user_id' => $user->id]));
        }
    }
}
