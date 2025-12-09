<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test2@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        User::create([
            'name' => 'test3',
            'email' => 'test3@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
