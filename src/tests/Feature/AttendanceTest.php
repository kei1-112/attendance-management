<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->seed(\StatusTableSeeder::class);

        $response = $this->get('/attendance');

        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance');

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'leaving_at' => now(),
            'status_id' => 4,
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->seed(\StatusTableSeeder::class);

        Carbon::setTestNow('2025-12-01 09:00:00');
        $response = $this->actingAs($user)->post('/attendance');

        $response = $this->get('/attendance/list?month=2025-12');

        $response->assertSee('12/1');
        $response->assertSee('09:00');

    }
}
