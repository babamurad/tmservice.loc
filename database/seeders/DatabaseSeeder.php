<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CitySeeder::class,
            CategorySeeder::class,
        ]);

        User::factory()->admin()->create([
            'phone' => 'babamurad2010@yandex.ru',
            'password' => bcrypt('password'),
        ]);
    }
}
