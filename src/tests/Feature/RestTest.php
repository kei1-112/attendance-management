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

class RestTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'status_id' => 2,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/rest_start');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'status_id' => 2,
        ]);

        $response = $this->actingAs($user)->post('/rest_start');
        $response = $this->actingAs($user)->post('/rest_end');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'status_id' => 2,
        ]);

        $response = $this->actingAs($user)->post('/rest_start');
        $response = $this->actingAs($user)->post('/rest_end');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'status_id' => 2,
        ]);

        $response = $this->actingAs($user)->post('/rest_start');
        $response = $this->actingAs($user)->post('/rest_end');

        $response = $this->actingAs($user)->post('/rest_start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00',
            'status_id' => 2,
        ]);


        Carbon::setTestNow('2025-12-01 12:00:00');
        $response = $this->actingAs($user)->post('/rest_start');

        Carbon::setTestNow('2025-12-01 13:00:00');
        $response = $this->actingAs($user)->post('/rest_end');

        $response = $this->get('/attendance/list?month=2025-12');
        //1時間分の休憩が記録されていることを確認
        $response->assertSee('01:00');
    }
}
