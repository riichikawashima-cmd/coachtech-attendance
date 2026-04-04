<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    public function test_自分が行った勤怠情報が全て表示されている(): void
    {
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('H:i'));
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m'));
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance/list?month=' . now()->subMonth()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee(now()->subMonth()->format('Y/m'));
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance/list?month=' . now()->addMonth()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee(now()->addMonth()->format('Y/m'));
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する(): void
    {
        $user = $this->createUser();

        $date = now()->toDateString();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('/attendance/' . $date);
    }
}
