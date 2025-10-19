<?php

namespace Database\Seeders;

use App\Models\User\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private function defaultUser(): User
    {
        return User::query()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->defaultUser();

        $this->call([
            ProductSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                ClothesSeeder::class,
            ]);
        }
    }
}
