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

    public function test_出勤ボタンで出勤できる(): void
    {
        $user = $this->createVerifiedUser();

        $this->actingAs($user);
        $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    public function test_出勤は1日1回のみ(): void
    {
        $user = $this->createVerifiedUser('test2@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/clock-in');

        $this->assertEquals(1, Attendance::count());
    }

    public function test_出勤時に_clock_in_が保存される(): void
    {
        $user = $this->createVerifiedUser('test3@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_in);
    }

    public function test_退勤できる(): void
    {
        $user = $this->createVerifiedUser('test4@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/clock-out');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_退勤時に_clock_out_が保存される(): void
    {
        $user = $this->createVerifiedUser('test5@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/clock-out');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_休憩開始できる(): void
    {
        $user = $this->createVerifiedUser('test6@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/break-start');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    public function test_休憩開始時に_break_start_が保存される(): void
    {
        $user = $this->createVerifiedUser('test7@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/break-start');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_start);
    }

    public function test_休憩終了できる(): void
    {
        $user = $this->createVerifiedUser('test8@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_end);
    }

    public function test_休憩終了時に_break_end_が保存される(): void
    {
        $user = $this->createVerifiedUser('test9@example.com');

        $this->actingAs($user);
        $this->post('/attendance/clock-in');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();

        $this->assertNotNull($break);
        $this->assertNotNull($break->break_end);
    }

    public function test_現在の日時情報がUIと同じ形式で出力される(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 8, 30, 0));

        $user = $this->createVerifiedUser('test10@example.com');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年3月25日(水)');
        $response->assertSee('08:30');

        Carbon::setTestNow();
    }

    public function test_勤務外の場合ステータスが勤務外と表示される(): void
    {
        $user = $this->createVerifiedUser('status1@example.com');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合ステータスが出勤中と表示される(): void
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

    public function test_休憩中の場合ステータスが休憩中と表示される(): void
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

    public function test_退勤済の場合ステータスが退勤済と表示される(): void
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
}
