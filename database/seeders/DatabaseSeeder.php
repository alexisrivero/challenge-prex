<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;

final class DatabaseSeeder extends Seeder
{
    /**
     * Creates the demo credentials used by the README and by the Postman
     * collection. It is idempotent, so it can run on every deployment.
     */
    public function run(): void
    {
        UserModel::query()->updateOrCreate(
            ['email' => (string) env('DEMO_USER_EMAIL', 'demo@prex.local')],
            [
                'name' => 'Prex Demo User',
                'password' => Hash::make((string) env('DEMO_USER_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );
    }
}
