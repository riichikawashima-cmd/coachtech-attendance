<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

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
}
