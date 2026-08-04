<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Данные для ручного тестирования приложения — рассчитано на свежую БД
 * (php artisan migrate:fresh --seed), запускать поверх уже заполненной
 * не нужно (упрётся в unique-констрейнт на phone).
 */
class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    private const PASSWORD = 'password';

    public function run(): void
    {
        $turkmenabat = City::where('name_ru', 'Туркменабад')->firstOrFail();
        $ashgabat = City::where('name_ru', 'Ашхабад')->firstOrFail();

        // Демонстрация посёлков-спутников (см. plan/README.md) — Фарап
        // числится отдельным городом в поиске (city_id узнаваем), но мастер
        // из него обязан попадать в выдачу по city_id=Туркменабад тоже.
        $farap = City::firstOrCreate(
            ['name_ru' => 'Фарап'],
            ['name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id],
        );

        $plumber = Category::where('name_ru', 'Сантехник')->firstOrFail();
        $electrician = Category::where('name_ru', 'Электрик')->firstOrFail();
        $autoRepair = Category::where('name_ru', 'Ремонт авто')->firstOrFail();
        $furniture = Category::where('name_ru', 'Сборка мебели')->firstOrFail();

        $masterProfiles = [];

        foreach ([
            ['phone' => '+993610000010', 'city' => $turkmenabat, 'category' => $plumber, 'is_free' => true, 'bio' => 'Меняю краны, чиню бойлеры и стиральные машины. Выезд по городу в течение часа.'],
            ['phone' => '+993610000011', 'city' => $turkmenabat, 'category' => $electrician, 'is_free' => false, 'bio' => 'Электромонтаж под ключ: проводка, розетки, счётчики.'],
            ['phone' => '+993610000012', 'city' => $turkmenabat, 'category' => $autoRepair, 'is_free' => true, 'bio' => 'Ремонт двигателя и ходовой части, выезд с инструментом.'],
            ['phone' => '+993610000013', 'city' => $ashgabat, 'category' => $furniture, 'is_free' => true, 'bio' => 'Сборка и ремонт мебели любой сложности.'],
            ['phone' => '+993610000014', 'city' => $farap, 'category' => $plumber, 'is_free' => true, 'bio' => 'Сантехник в Фарапе, выезжаю и в Туркменабад.'],
        ] as $data) {
            $user = User::factory()->master()->create([
                'phone' => $data['phone'],
                'password' => Hash::make(self::PASSWORD),
            ]);
            $user->markPhoneAsVerified();

            $profile = $user->masterProfile()->create([
                'city_id' => $data['city']->id,
                'category_id' => $data['category']->id,
                'bio' => $data['bio'],
                'is_free' => $data['is_free'],
            ]);
            $profile->approve();

            $masterProfiles[] = $profile;
        }

        $clients = [];

        foreach (['+993610000020', '+993610000021'] as $phone) {
            $client = User::factory()->create([
                'phone' => $phone,
                'password' => Hash::make(self::PASSWORD),
            ]);
            $client->markPhoneAsVerified();

            $clients[] = $client;
        }

        // Пара уже одобренных отзывов — рейтинг виден сразу, без ручной модерации.
        $review = $masterProfiles[0]->reviews()->create([
            'client_id' => $clients[0]->id,
            'rating' => 5,
            'comment' => 'Приехал быстро, всё починил, буду обращаться ещё.',
        ]);
        $review->approve();

        $review2 = $masterProfiles[2]->reviews()->create([
            'client_id' => $clients[1]->id,
            'rating' => 4,
            'comment' => 'Хорошая работа, но опоздал на полчаса.',
        ]);
        $review2->approve();

        $this->command?->info('');
        $this->command?->info('Демо-аккаунты (пароль у всех: '.self::PASSWORD.'):');
        $this->command?->table(
            ['Роль', 'Телефон', 'Заметка'],
            [
                ['admin', '+993610000001', 'создан в DatabaseSeeder'],
                ['master', '+993610000010', 'Сантехник, Туркменабад, свободен, 1 отзыв (5★)'],
                ['master', '+993610000011', 'Электрик, Туркменабад, занят'],
                ['master', '+993610000012', 'Ремонт авто, Туркменабад, свободен, 1 отзыв (4★)'],
                ['master', '+993610000013', 'Сборка мебели, Ашхабад, свободен'],
                ['master', '+993610000014', 'Сантехник, Фарап (посёлок Туркменабада), свободен'],
                ['client', '+993610000020', 'уже оставил отзыв мастеру #1'],
                ['client', '+993610000021', 'уже оставил отзыв мастеру #3, может оставить ещё'],
            ],
        );
    }
}
