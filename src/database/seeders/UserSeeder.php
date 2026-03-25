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
        for ($i = 1; $i <= 20; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@test.com"],
                [
                    'name' => "テストユーザー{$i}",
                    'password' => Hash::make('password123'),
                    'email_verified_at' => Carbon::now(),
                ]
            );
        }
    }
}
