<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_登録内容と一致しない場合、バリデーションメッセージが表示される(): void
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_正しい情報ならログインできる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/attendance');

        $this->assertAuthenticatedAs($user);
    }
}
