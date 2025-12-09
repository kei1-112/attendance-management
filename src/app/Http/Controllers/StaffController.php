<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class StaffController extends Controller
{
    public function list(){
        $users = User::all();
        return view('staff_list', compact('users'));
    }

    public function attendance(Request $request, $id){
        //勤怠情報の取得
        $user = User::where('id', '=', $id)->first();
        $monthString = $request->query('month');
        $month = Carbon::createFromFormat('Y-m', $monthString);
        $tmpAttendances = Attendance::where('user_id', '=', $id)
                                        ->where('attendance_at','LIKE', "%{$month}%")
                                        ->get();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $dates = collect();
        $attendances = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tmpDate = $date->format('Y-m-d');
            $tmpRest = null;
            $attendanceSum = null;

            //その日の勤怠情報を取得
            $tmpAttendance = Attendance::where('user_id', '=', $id)
                                        ->where('attendance_at','LIKE', "%{$tmpDate}%")
                                        ->first();

            //その日の勤怠情報が存在する時、休憩時間と合計の出勤時間を計算する
            if($tmpAttendance){
                $tmpAttendanceId = $tmpAttendance->id;

                //その日の勤怠IDに紐づく休憩時間の計算
                $rests = Rest::where('attendance_id', '=', $tmpAttendanceId)
                                ->get();

                if(!$rests->isEmpty()){
                    foreach($rests as $rest){
                        $restStart = date('Y-m-d H:i:00', strtotime($rest['rest_start_at']));
                        $restEnd = date('Y-m-d H:i:00', strtotime($rest['rest_end_at']));
                        $restDiff = strtotime($restEnd) - strtotime($restStart);
                        $tmpRest = $tmpRest + $restDiff;
                    }
                }

                 //その日の勤怠の合計を計算
                $tmpAttendanceAt = date('Y-m-d H:i:00', strtotime($tmpAttendance['attendance_at']));
                $tmpLeavingAt = date('Y-m-d H:i:00', strtotime($tmpAttendance['leaving_at']));
                $attendanceDiff = strtotime($tmpLeavingAt) - strtotime($tmpAttendanceAt);
                $attendanceSum = $attendanceDiff - $tmpRest;
            }

            $tmpDate = $date->format('Y-m-d H:i:s');
            $attendances[$tmpDate] = [
                'id' =>$tmpAttendance->id ?? null,
                'date' => $tmpDate,
                'attendance_at' => $tmpAttendance->attendance_at ?? null,
                'leaving_at' => $tmpAttendance->leaving_at ?? null,
                'rests' => $tmpRest ?? null,
                'attendance_sum' => $attendanceSum??null,
            ];
        }

        ksort($attendances);
        return view('staff_attendance', compact('attendances', 'month', 'user'));
    }
}
