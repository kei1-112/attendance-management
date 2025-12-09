<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminDetailTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $response = $this->get('/admin/attendance/' . $attendance['id']);
        $response->assertSee('勤怠詳細');
        $response->assertSee('TestUser');
        $response->assertSee('2025年　　　　　12月01日');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '19:00',
            'leaving_at' => '18:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response = $this->post('admin/stamp_correction', $correctAttendanceData);

        $response = $this->get('/admin/attendance/' . $attendance['id']);
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '19:00',
            'rest_end_at' => '17:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response = $this->post('admin/stamp_correction', $correctAttendanceData);

        $response = $this->get('/admin/attendance/' . $attendance['id']);
        $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '19:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response = $this->post('admin/stamp_correction', $correctAttendanceData);

        $response = $this->get('/admin/attendance/' . $attendance['id']);
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => '',
        ];

        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response = $this->post('/admin/stamp_correction', $correctAttendanceData);
        $response = $this->get('/admin/attendance/' . $attendance['id']);

        $response->assertSee('備考を記入してください');
    }
}
