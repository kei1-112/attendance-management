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
            'id' => 1,
            'status' => '勤務外'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'id' => 2,
            'status' => '出勤中'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'id' => 3,
            'status' => '休憩中'
        ];
        DB::table('statuses')->insert($param);
        $param = [
            'id' => 4,
            'status' => '退勤済'
        ];
        DB::table('statuses')->insert($param);
    }
}
