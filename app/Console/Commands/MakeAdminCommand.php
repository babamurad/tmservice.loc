<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    protected $signature = 'make:admin {phone}';

    protected $description = 'Создать пользователя с ролью admin (или повысить существующего) — единственный путь получить эту роль, кроме сидера';

    public function handle(): int
    {
        $phone = $this->argument('phone');

        $user = User::where('phone', $phone)->first();

        if ($user) {
            if ($user->role === 'admin') {
                $this->info("Пользователь {$phone} уже admin.");

                return self::SUCCESS;
            }

            if (! $this->confirm("Пользователь {$phone} существует с ролью \"{$user->role}\". Сделать его admin?")) {
                return self::FAILURE;
            }

            $user->update(['role' => 'admin']);
            $this->info("Пользователь {$phone} теперь admin.");

            return self::SUCCESS;
        }

        $password = $this->secret('Пароль для нового admin');

        $validator = Validator::make(
            ['password' => $password],
            ['password' => 'required|string|min:6'],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        User::create([
            'phone' => $phone,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Создан новый admin: {$phone}.");

        return self::SUCCESS;
    }
}
