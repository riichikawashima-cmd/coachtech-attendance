<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
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

    private function createUser($email = 'user@test.com')
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる(): void
    {
        $admin = $this->createAdmin();

        $user1 = $this->createUser('user1@test.com');
        $user2 = $this->createUser('user2@test.com');

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);
    }

    public function test_ユーザーの勤怠情報が正しく表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
            'clock_in' => Carbon::parse('2026-03-25 09:00'),
            'clock_out' => Carbon::parse('2026-03-25 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-25',
            'clock_in' => Carbon::parse('2026-02-25 09:00'),
            'clock_out' => Carbon::parse('2026-02-25 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-02');

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-25',
            'clock_in' => Carbon::parse('2026-04-25 09:00'),
            'clock_out' => Carbon::parse('2026-04-25 18:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $date = '2026-03-25';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '/' . $date);

        $response->assertStatus(200);
    }
}
