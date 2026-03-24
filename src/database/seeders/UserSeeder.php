<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'テストユーザー',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ]
        );
    }
}
