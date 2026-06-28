<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat'],
            ['name_ru' => 'Ашхабад', 'name_tm' => 'Aşgabat'],
            ['name_ru' => 'Мары', 'name_tm' => 'Mary'],
            ['name_ru' => 'Дашогуз', 'name_tm' => 'Daşoguz'],
            ['name_ru' => 'Балканабад', 'name_tm' => 'Balkanabat'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
