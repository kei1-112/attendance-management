<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Carbon\Carbon;

class CheckStatusTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::now();

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合、勤怠ステータスが正しく表示される()
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

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => now(),
            'status_id' => 3,
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合、勤怠ステータスが正しく表示される()
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

        $response->assertSee('退勤済');
    }
}
