<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class GetAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('TestUser');
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('2025年　　　　　12月01日');
    }

    /** @test */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        Rest::create([
            'attendance_id' => $attendance['id'],
            'rest_start_at' => '2025-12-01 12:00',
            'rest_end_at' => '2025-12-01 13:00',
        ]);

        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
