<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\Admin;
use Carbon\Carbon;

class AdminGetAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $user1 = User::create([
            'name' => 'TestUser1',
            'email' => 'test1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'TestUser2',
            'email' => 'test2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user1->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'attendance_at' => '2025-12-01 10:00:00',
            'leaving_at' => '2025-12-01 19:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('admin/attendance/list?date=2025-12-01');

        $response->assertSee('2025年12月01日の勤怠');
        $response->assertSee('TestUser1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('TestUser2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        Carbon::setTestNow('2025-12-01 09:00:00');

        $response = $this->get('admin/attendance/list');

        $response->assertSee('2025年12月01日の勤怠');
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $user1 = User::create([
            'name' => 'TestUser1',
            'email' => 'test1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'TestUser2',
            'email' => 'test2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user1->id,
            'attendance_at' => '2025-11-30 09:00:00',
            'leaving_at' => '2025-11-30 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'attendance_at' => '2025-11-30 10:00:00',
            'leaving_at' => '2025-11-30 19:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('admin/attendance/list?date=2025-12-01');

        $date = Carbon::createFromFormat('Y-m-d', '2025-12-01');

        $response = $this->get('/admin/attendance/list?date=' . $date->copy()->subDay()->format('Y-m-d'));

        $response->assertSee('2025年11月30日の勤怠');
        $response->assertSee('TestUser1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('TestUser2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $user1 = User::create([
            'name' => 'TestUser1',
            'email' => 'test1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'TestUser2',
            'email' => 'test2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user1->id,
            'attendance_at' => '2025-12-02 09:00:00',
            'leaving_at' => '2025-12-02 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'attendance_at' => '2025-12-02 10:00:00',
            'leaving_at' => '2025-12-02 19:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('admin/attendance/list?date=2025-12-01');

        $date = Carbon::createFromFormat('Y-m-d', '2025-12-01');

        $response = $this->get('/admin/attendance/list?date=' . $date->copy()->addDay()->format('Y-m-d'));

        $response->assertSee('2025年12月02日の勤怠');
        $response->assertSee('TestUser1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('TestUser2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
