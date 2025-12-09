<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => 'admin1',
            'email' => 'admin1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => 'admin2',
            'email' => 'admin2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin2@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => 'admin',
            'email' => 'admin3@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
