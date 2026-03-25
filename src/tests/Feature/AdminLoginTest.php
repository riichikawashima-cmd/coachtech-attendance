<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
