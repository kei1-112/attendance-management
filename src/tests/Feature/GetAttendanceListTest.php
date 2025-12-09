<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use Carbon\Carbon;

class GetAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-02 10:00:00',
            'leaving_at' => '2025-12-02 19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->get('/attendance/list?month=2025-12');

        $response->assertSee('12/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12/02');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 勤怠一覧に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2025-12-01 09:00:00');

        $response = $this->get('/attendance/list?month=' . \Carbon\Carbon::now()->format('Y-m'));

        $response->assertSee('2025/12');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2025-12-01 09:00:00');
        $month = Carbon::createFromFormat('Y-m', '2025-12');

        $response = $this->get('/attendance/list?month=' . \Carbon\Carbon::now()->format('Y-m'));

        $response = $this->get('/attendance/list?month=' . $month->copy()->subMonth()->format('Y-m'));

        $response->assertSee('2025/11');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2025-12-01 09:00:00');
        $month = Carbon::createFromFormat('Y-m', '2025-12');

        $response = $this->get('/attendance/list?month=' . \Carbon\Carbon::now()->format('Y-m'));

        $response = $this->get('/attendance/list?month=' . $month->copy()->addMonth()->format('Y-m'));

        $response->assertSee('2026/01');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
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

        $response = $this->get('/attendance/list?month=2025-12');

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        $response->assertSee('勤怠詳細');
        $response->assertSee('2025年　　　　　12月01日');
    }
}