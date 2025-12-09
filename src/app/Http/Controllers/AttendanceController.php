<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as RequestModel;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $userId = Auth::id();
        $today = date('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();
        return view('attendance', compact('todayAttendance'));
    }

    public function store()
    {
        $attendance['user_id'] = Auth::id();
        $attendance['attendance_at'] = Carbon::now();
        $attendance['status_id'] = 2; //2:出勤中
        Attendance::create($attendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function restStart()
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $rest['attendance_id'] = $todayAttendance->id;
        $rest['rest_start_at'] = Carbon::now();
        Rest::create($rest);

        $attendance['status_id'] = 3; //3:休憩中
        Attendance::where('user_id', '=', $userId)
                    ->where('attendance_at','LIKE', "%{$today}%")
                    ->update($attendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function restEnd()
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $latestRest = Rest::where('attendance_id', $todayAttendance['id'])->latest()->first();
        $rest['rest_end_at'] = Carbon::now();
        Rest::find($latestRest['id'])->update($rest);

        $attendance['status_id'] = 2; //2:勤務中
        Attendance::where('user_id', '=', $userId)
                    ->where('attendance_at','LIKE', "%{$today}%")
                    ->update($attendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function leave()
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $attendance['status_id'] = 4; //4:退勤済
        $attendance['leaving_at'] = Carbon::now();

        Attendance::where('user_id', '=', $userId)
                    ->where('attendance_at','LIKE', "%{$today}%")
                    ->update($attendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function list(Request $request)
    {
        $userId = Auth::id();
        $monthString = $request->query('month');
        $month = Carbon::createFromFormat('Y-m', $monthString);
        $tmpAttendances = Attendance::where('user_id', '=', $userId)
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
            $tmpAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$tmpDate}%")
                                        ->first();

            //その日の勤怠情報が存在する時、休憩時間と合計の出勤時間を計算する
            if($tmpAttendance){
            $tmpAttendanceId = $tmpAttendance->id;

                //その日の勤怠IDに紐づく休憩時間の計算
                $rests = Rest::where('attendance_id', '=', $tmpAttendanceId)
                                ->get();
                $latestRest = Rest::where('attendance_id', $tmpAttendanceId)
                                    ->orderBy('created_at', 'desc')
                                    ->first();

                if(!$rests->isEmpty()){
                    if($latestRest['rest_end_at'] != null){
                        foreach($rests as $rest){
                            // $restStart = date('Y-m-d H:i:00', strtotime($rest['rest_start_at']));
                            // $restEnd = date('Y-m-d H:i:00', strtotime($rest['rest_end_at']));
                            $restStart = \Carbon\Carbon::parse($rest['rest_start_at'])->format('Y-m-d H:i:00');
                            $restEnd = \Carbon\Carbon::parse($rest['rest_end_at'])->format('Y-m-d H:i:00');
                            $restDiff = strtotime($restEnd) - strtotime($restStart);
                            $tmpRest = $tmpRest + $restDiff;
                        }
                    }
                }

                 //その日の勤怠の合計を計算
                if($tmpAttendance['leaving_at'] != null){
                    $tmpAttendanceAt = date('Y-m-d H:i:00', strtotime($tmpAttendance['attendance_at']));
                    $tmpLeavingAt = date('Y-m-d H:i:00', strtotime($tmpAttendance['leaving_at']));
                    $attendanceDiff = strtotime($tmpLeavingAt) - strtotime($tmpAttendanceAt);
                    $attendanceSum = $attendanceDiff - $tmpRest;
                }
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
        return view('attendance_list', compact('attendances', 'month'));
    }

    public function detail($id){
        if(strpos($id, '-') !== false){
            $date = $id;
            $userId = Auth::id();
            $name = User::find($userId)->name;
            return view('attendance_detail', compact('date', 'name'));
        }
        $attendance = Attendance::where('id', '=', $id)
                                    ->first();
        $rests = Rest::where('attendance_id', '=', $id)
                    ->get();
        $latestRequest = RequestModel::where('attendance_id', '=', $id)
                            ->latest()
                            ->first();
        //新しく勤怠を登録する場合と区別するために入れる
        $date = null;
        return view('attendance_detail', compact('attendance', 'rests', 'latestRequest', 'date'));
    }
}
