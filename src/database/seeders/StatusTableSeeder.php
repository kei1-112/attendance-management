<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'status' => '勤務外'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'status' => '出勤中'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'status' => '休憩中'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'status' => '退勤済'
        ];
        DB::table('statuses')->insert($param);
    }
}
