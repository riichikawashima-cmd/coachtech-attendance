<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@test.com')->first();

        if (!$user) return;

        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::now()->subDays($i);

            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    'note' => 'テストデータ',
                ]
            );
        }
    }
}
