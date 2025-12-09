<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ログイン確認用のユーザーを1件だけ作成
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // ログイン確認用の管理者ユーザーを1件だけ作成
        Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->call(StatusTableSeeder::class);

        //勤怠情報のダミーデータを作成
        Attendance::create([
            'user_id' => 1,
            'attendance_at' => '2025-11-25 09:00:00',
            'leaving_at' => '2025-11-25 18:00:00',
            'status_id' => 4,
        ]);

        Rest::create([
            'attendance_id' => 1,
            'rest_start_at' => '2025-11-25 12:00:00',
            'rest_end_at' => '2025-11-25 13:00:00',
        ]);

        Attendance::create([
            'user_id' => 1,
            'attendance_at' => '2025-11-26 09:00:00',
            'leaving_at' => '2025-11-26 18:00:00',
            'status_id' => 4,
        ]);

        Rest::create([
            'attendance_id' => 2,
            'rest_start_at' => '2025-11-26 12:00:00',
            'rest_end_at' => '2025-11-26 13:00:00',
        ]);

        Attendance::create([
            'user_id' => 1,
            'attendance_at' => '2025-11-27 09:00:00',
            'leaving_at' => '2025-11-27 18:00:00',
            'status_id' => 4,
        ]);

        Rest::create([
            'attendance_id' => 3,
            'rest_start_at' => '2025-11-27 12:00:00',
            'rest_end_at' => '2025-11-27 13:00:00',
        ]);

        Attendance::create([
            'user_id' => 1,
            'attendance_at' => '2025-11-28 09:00:00',
            'leaving_at' => '2025-11-28 18:00:00',
            'status_id' => 4,
        ]);

        Rest::create([
            'attendance_id' => 4,
            'rest_start_at' => '2025-11-28 12:00:00',
            'rest_end_at' => '2025-11-28 13:00:00',
        ]);
    }
}
