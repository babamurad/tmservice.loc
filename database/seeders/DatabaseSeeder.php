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

        // 'phone' раньше был email-адресом ('babamurad2010@yandex.ru') — это
        // поле для входа по номеру телефона, а не контактная почта. Логин
        // технически работал (сверяется просто строка), но реальным номером
        // это никогда не было — путало при ручном тестировании.
        User::factory()->admin()->create([
            'phone' => '+993610000001',
            'password' => bcrypt('password'),
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
