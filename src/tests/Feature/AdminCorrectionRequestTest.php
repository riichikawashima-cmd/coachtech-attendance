<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AdminCorrectionRequestTest extends TestCase
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

    public function test_承認待ちの修正申請が全て表示されている(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => now(),
            'requested_clock_out' => now(),
            'requested_note' => '修正理由テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('修正理由テスト');
    }

    public function test_承認済みの修正申請が全て表示されている(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => now(),
            'requested_clock_out' => now(),
            'requested_note' => '承認済みテスト',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト');
    }

    public function test_修正申請の詳細内容が正しく表示されている(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $request = CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse('2026-03-25 09:00'),
            'requested_clock_out' => Carbon::parse('2026-03-25 18:00'),
            'requested_note' => '詳細テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(200);
        $response->assertSee('詳細テスト');
    }

    public function test_修正申請の承認処理が正しく行われる(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-25',
        ]);

        $request = CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse('2026-03-25 09:00'),
            'requested_clock_out' => Carbon::parse('2026-03-25 18:00'),
            'requested_note' => '承認テスト',
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/stamp_correction_request/approve/' . $request->id);

        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
