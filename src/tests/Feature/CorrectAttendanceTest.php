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

class CorrectAttendanceTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '19:00',
            'leaving_at' => '18:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        $response = $this->post('stamp_correction_request', $correctAttendanceData);
        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '19:00',
            'rest_end_at' => '17:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        $response = $this->post('stamp_correction_request', $correctAttendanceData);
        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '19:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        $response = $this->post('stamp_correction_request', $correctAttendanceData);
        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => '',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        $response = $this->post('stamp_correction_request', $correctAttendanceData);
        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('備考を記入してください');
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::create([
            'name' => 'TestUser',
            'email' => 'test@example.com',
            'email_verified_at' => '2025-01-01 0:00:00',
            'password' => bcrypt('password'),
        ]);

        $admin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        Carbon::setTestNow('2025-12-02 09:00:00');

        $response = $this->post('stamp_correction_request', $correctAttendanceData);

        $requestId = Request::latest()->value('id');

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('TestUser');
        $response->assertSee('2025/12/01');

        $response = $this->get('/stamp_correction_request/approve/' . $requestId);
        $response->assertSee('TestUser');
        $response->assertSee('2025年　　　　　12月01日');
    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        Carbon::setTestNow('2025-12-02 09:00:00');

        $response = $this->post('stamp_correction_request', $correctAttendanceData);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('TestUser');
        $response->assertSee('2025/12/01');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        Carbon::setTestNow('2025-12-02 09:00:00');

        $response = $this->post('stamp_correction_request', $correctAttendanceData);

        $requestId = Request::latest()->value('id');

        //リクエストを承認済みにする
        Request::where('id', $requestId)->update([
            'approval_flag' => 0 //承認済み
        ]);

        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertSee('TestUser');
        $response->assertSee('2025/12/01');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
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

        $correctAttendanceData = [
            'date' => '2025-12-01',
            'attendance_id' => $attendance['id'],
            'attendance_at' => '09:00',
            'leaving_at' => '18:00',
            'rest_start_at' => '13:00',
            'rest_end_at' => '14:00',
            'remarks' => 'test',
        ];

        $response = $this->get('/attendance/detail/' . $attendance['id']);

        Carbon::setTestNow('2025-12-02 09:00:00');

        $response = $this->post('stamp_correction_request', $correctAttendanceData);

        $response = $this->get('/stamp_correction_request/list');
        $response = $this->get('/attendance/detail/' . $attendance['id']);
        $response->assertSee('勤怠詳細');
        $response->assertSee('TestUser');
        $response->assertSee('2025年　　　　　12月01日');
    }
}
