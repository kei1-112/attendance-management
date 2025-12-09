<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class MailAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', '=', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', '=', 'test@example.com')->first();

        $verificationUrl = null;

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->email),
            ]
        );

        $this->actingAs($user);
        $response = $this->get('/email/verify');

        // ボタンが mailhog に飛ぶリンクを持っているかを確認
        $response->assertSee('http://localhost:8025', false);
    }

    /** @test */
    public function メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', '=', 'test@example.com')->first();

        $verificationUrl = null;

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->email),
            ]
        );

        $this->actingAs($user);
        $response = $this->get($verificationUrl);
        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
        $response->assertSee('出勤');
    }
}
