<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
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

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
            'clock_in' => Carbon::parse('2026-03-25 09:00'),
            'clock_out' => Carbon::parse('2026-03-25 18:00'),
            'note' => 'テスト備考',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テスト備考');
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/attendance/' . $attendance->id, [
                'date' => '2026-03-25',
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['clock_in']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/attendance/' . $attendance->id, [
                'date' => '2026-03-25',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '20:00',
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/attendance/' . $attendance->id, [
                'date' => '2026-03-25',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '19:00',
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/attendance/' . $attendance->id, [
                'date' => '2026-03-25',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors(['note']);
    }
}
