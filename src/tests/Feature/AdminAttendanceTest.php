<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function createUser()
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $date = '2026-03-25';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_遷移した際に現在の日付が表示される(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25));

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/03/25');

        Carbon::setTestNow();
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $date = '2026-03-24';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $date = '2026-03-26';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}
