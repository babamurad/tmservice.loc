<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik'],
            ['name_ru' => 'Электрик', 'name_tm' => 'Elektrik'],
            ['name_ru' => 'Ремонт авто', 'name_tm' => 'Awtoremont'],
            ['name_ru' => 'Сборка мебели', 'name_tm' => 'Meşrep ýygnamak'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
