<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(string $email = 'test@example.com'): User
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    public function test_現在の日時情報がUIと同じ形式で出力される(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 8, 30, 0));

        $user = $this->createVerifiedUser('test_datetime@example.com');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年3月25日(水)');
        $response->assertSee('08:30');

        Carbon::setTestNow();
    }

    public function test_勤務外の場合_勤怠ステータスが正しく表示される(): void
    {
        $user = $this->createVerifiedUser('status1@example.com');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合_勤怠ステータスが正しく表示される(): void
    {
        $user = $this->createVerifiedUser('status2@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合_勤怠ステータスが正しく表示される(): void
    {
        $user = $this->createVerifiedUser('status3@example.com');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合_勤怠ステータスが正しく表示される(): void
    {
        $user = $this->createVerifiedUser('status4@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }

    public function test_出勤ボタンが正しく機能する(): void
    {
        $user = $this->createVerifiedUser('clockin_action@example.com');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    public function test_出勤は一日一回のみできる(): void
    {
        $user = $this->createVerifiedUser('clockin_once@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertDontSee('>出勤<', false);

        $this->assertEquals(1, Attendance::count());
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 8, 30, 0));

        $user = $this->createVerifiedUser('clockin_list@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('08:30');

        Carbon::setTestNow();
    }

    public function test_休憩ボタンが正しく機能する(): void
    {
        $user = $this->createVerifiedUser('break_start_action@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    public function test_休憩は一日に何回でもできる(): void
    {
        $user = $this->createVerifiedUser('break_multiple@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');
        $this->post('/attendance/break-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
    }

    public function test_休憩戻ボタンが正しく機能する(): void
    {
        $user = $this->createVerifiedUser('break_end_action@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->post('/attendance/break-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/attendance/break-end');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_end);
    }

    public function test_休憩戻は一日に何回でもできる(): void
    {
        $user = $this->createVerifiedUser('break_end_multiple@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $breaks = AttendanceBreak::where('attendance_id', $attendance->id)->get();

        $this->assertCount(2, $breaks);
        $this->assertNotNull($breaks[0]->break_end);
        $this->assertNotNull($breaks[1]->break_end);
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 8, 30, 0));

        $user = $this->createVerifiedUser('break_list@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 3, 25, 12, 0, 0));
        $this->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 3, 25, 13, 0, 0));
        $this->post('/attendance/break-end');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('1:00');

        Carbon::setTestNow();
    }

    public function test_退勤ボタンが正しく機能する(): void
    {
        $user = $this->createVerifiedUser('clockout_action@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 8, 30, 0));

        $user = $this->createVerifiedUser('clockout_list@example.com');

        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 3, 25, 17, 30, 0));
        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('17:30');

        Carbon::setTestNow();
    }
}
