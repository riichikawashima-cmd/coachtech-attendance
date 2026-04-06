<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email = 'test@example.com'): User
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

    public function test_勤怠詳細画面の_名前_がログインユーザーの氏名になっている(): void
    {
        $user = $this->createUser('detail_name@example.com');
        $date = '2026-03-25';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $date);

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    public function test_勤怠詳細画面の_日付_が選択した日付になっている(): void
    {
        $user = $this->createUser('detail_date@example.com');
        $date = '2026-03-25';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $date);

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('3月25日');
    }

    public function test_出勤_退勤にて記されている時間がログインユーザーの打刻と一致している(): void
    {
        $user = $this->createUser('detail_clock@example.com');
        $date = '2026-03-25';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $date);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩にて記されている時間がログインユーザーの打刻と一致している(): void
    {
        $user = $this->createUser('detail_break@example.com');
        $date = '2026-03-25';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse($date . ' 12:00'),
            'break_end' => Carbon::parse($date . ' 13:00'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $date);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される(): void
    {
        $user = $this->createUser('apply_clock_error@example.com');
        $date = '2026-03-25';

        $response = $this->actingAs($user)->post('/attendance/' . $date . '/apply', [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される(): void
    {
        $user = $this->createUser('apply_break_start_error@example.com');
        $date = '2026-03-25';

        $response = $this->actingAs($user)->post('/attendance/' . $date . '/apply', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break1_start' => '19:00',
            'break1_end' => '19:30',
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['break1_start']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される(): void
    {
        $user = $this->createUser('apply_break_end_error@example.com');
        $date = '2026-03-25';

        $response = $this->actingAs($user)->post('/attendance/' . $date . '/apply', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break1_start' => '17:30',
            'break1_end' => '18:30',
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['break1_start']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される(): void
    {
        $user = $this->createUser('apply_remark_error@example.com');
        $date = '2026-03-25';

        $response = $this->actingAs($user)->post('/attendance/' . $date . '/apply', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark']);
    }

    public function test_修正申請処理が実行される(): void
    {
        $user = $this->createUser('apply_success@example.com');
        $date = '2026-03-25';

        $response = $this->actingAs($user)->post('/attendance/' . $date . '/apply', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'remark' => 'テスト備考',
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $this->assertNotNull($attendance);

        $this->assertDatabaseHas('correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_note' => 'テスト備考',
            'status' => 'pending',
        ]);
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されていること(): void
    {
        $user = $this->createUser('pending_user@example.com');
        $date = '2026-03-25';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse($date . ' 09:30'),
            'requested_clock_out' => Carbon::parse($date . ' 18:30'),
            'requested_note' => '承認待ちテスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('承認待ちテスト');
        $response->assertSee('2026/03/25');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている(): void
    {
        $user = $this->createUser('approved_user@example.com');
        $date = '2026-03-25';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse($date . ' 09:30'),
            'requested_clock_out' => Carbon::parse($date . ' 18:30'),
            'requested_note' => '承認済みテスト',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済みテスト');
        $response->assertSee('2026/03/25');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する(): void
    {
        $user = $this->createUser('detail_link_user@example.com');
        $date = '2026-03-25';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00'),
            'clock_out' => Carbon::parse($date . ' 18:00'),
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse($date . ' 09:30'),
            'requested_clock_out' => Carbon::parse($date . ' 18:30'),
            'requested_note' => '詳細リンクテスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('/attendance/' . $date);
    }
}
