<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Carbon\Carbon;

class LeaveTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 退勤ボタンが正しく機能する()
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
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/leave');
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->seed(\StatusTableSeeder::class);

        Carbon::setTestNow('2025-12-01 09:00:00');
        $response = $this->actingAs($user)->post('/attendance');

        Carbon::setTestNow('2025-12-01 18:00:00');
        $response = $this->actingAs($user)->post('/leave');

        $response = $this->get('/attendance/list?month=2025-12');

        $response->assertSee('12/1');
        $response->assertSee('18:00');

    }
}
