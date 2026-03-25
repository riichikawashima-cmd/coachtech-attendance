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
        $users = User::all();
        $date = Carbon::today();

        foreach ($users as $user) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'clock_in' => $date->copy()->setTime(rand(8, 10), 0),
                    'clock_out' => $date->copy()->setTime(rand(17, 20), 0),
                    'note' => 'ダミーデータ',
                ]
            );
        }
    }
}
