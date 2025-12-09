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

class AdminGetUserTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        User::create([
            'name' => 'TestUser1',
            'email' => 'test1@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'TestUser2',
            'email' => 'test2@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'TestUser3',
            'email' => 'test3@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('admin/staff/list');

        $response->assertSee('TestUser1');
        $response->assertSee('test1@example.com');
        $response->assertSee('TestUser2');
        $response->assertSee('test2@example.com');
        $response->assertSee('TestUser3');
        $response->assertSee('test3@example.com');
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/staff/' . $user['id'] . '?month=2025-12');

        $response->assertSee('12/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12/02');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-11-01 09:00:00',
            'leaving_at' => '2025-11-01 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-11-02 10:00:00',
            'leaving_at' => '2025-11-02 19:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $month = Carbon::createFromFormat('Y-m', '2025-12');

        $response = $this->get('/admin/attendance/staff/' . $user['id'] . '?month=' . $month->copy()->subMonth()->format('Y-m'));

        $response->assertSee('11/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('11/02');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        $this->seed(\StatusTableSeeder::class);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2026-01-01 09:00:00',
            'leaving_at' => '2026-01-01 18:00:00',
            'status_id' => 4,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2026-01-02 10:00:00',
            'leaving_at' => '2026-01-02 19:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $month = Carbon::createFromFormat('Y-m', '2025-12');

        $response = $this->get('/admin/attendance/staff/' . $user['id'] . '?month=' . $month->copy()->addMonth()->format('Y-m'));

        $response->assertSee('01/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('01/02');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'status_id' => 4,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/staff/' . $user['id'] . '?month=2025-12');
        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response->assertSee('勤怠詳細');
        $response->assertSee('2025年　　　　　12月01日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
