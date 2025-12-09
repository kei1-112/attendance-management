<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Admin;
use App\Models\Request;
use Carbon\Carbon;

class AdminCorrectAttendanceTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
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

        $attendance1 = Attendance::create([
            'user_id' => $user1['id'],
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'remarks' => 'test1',
            'status_id' => 4,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2['id'],
            'attendance_at' => '2025-12-01 10:00:00',
            'leaving_at' => '2025-12-01 19:00:00',
            'remarks' => 'test2',
            'status_id' => 4,
        ]);

        Request::create([
            'attendance_id' => $attendance1['id'],
            'requested_at' => '2025-12-01 19:00:00',
            'approval_flag' => 1,
        ]);

        Request::create([
            'attendance_id' => $attendance2['id'],
            'requested_at' => '2025-12-02 19:00:00',
            'approval_flag' => 1,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('stamp_correction_request/list');

        $response->assertSee('承認待ち');

        $response->assertSee('TestUser1');
        $response->assertSee('test1');

        $response->assertSee('TestUser2');
        $response->assertSee('test2');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
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

        $attendance1 = Attendance::create([
            'user_id' => $user1['id'],
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'remarks' => 'test1',
            'status_id' => 4,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2['id'],
            'attendance_at' => '2025-12-01 10:00:00',
            'leaving_at' => '2025-12-01 19:00:00',
            'remarks' => 'test2',
            'status_id' => 4,
        ]);

        Request::create([
            'attendance_id' => $attendance1['id'],
            'requested_at' => '2025-12-01 19:00:00',
            'approval_flag' => 0,
        ]);

        Request::create([
            'attendance_id' => $attendance2['id'],
            'requested_at' => '2025-12-02 19:00:00',
            'approval_flag' => 0,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('stamp_correction_request/list?tab=
        approved');

        $response->assertSee('承認済み');

        $response->assertSee('TestUser1');
        $response->assertSee('test1');

        $response->assertSee('TestUser2');
        $response->assertSee('test2');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user['id'],
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'remarks' => 'test1',
            'status_id' => 4,
        ]);

        $request = Request::create([
            'attendance_id' => $attendance['id'],
            'requested_at' => '2025-12-01 19:00:00',
            'approval_flag' => 1,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/approve/' . $request['id']);

        $response->assertSee('勤怠詳細');

        $response->assertSee('TestUser');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\StatusTableSeeder::class);

        $attendance = Attendance::create([
            'user_id' => $user['id'],
            'attendance_at' => '2025-12-01 09:00:00',
            'leaving_at' => '2025-12-01 18:00:00',
            'remarks' => 'test1',
            'status_id' => 4,
        ]);

        $request = Request::create([
            'attendance_id' => $attendance['id'],
            'requested_at' => '2025-12-01 19:00:00',
            'approval_flag' => 1,
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/approve/' . $request['id']);

        $response = $this->post('/stamp_correction_request/approve/' . $request['id']);

        // DBに更新されているか
        $this->assertDatabaseHas('requests', [
            'id' => $request['id'],
            'approval_flag' => 0,
        ]);
    }
}
