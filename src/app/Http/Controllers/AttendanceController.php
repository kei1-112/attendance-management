<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Attendance;
use App\Models\Rest;

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
        $attendance['attendance_at'] = date('Y-m-d H:i:s');
        $attendance['status_id'] = 2; //2:出勤中
        $attendance['request_flag'] = 0; //0:リクエストしていない
        Attendance::create($attendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function restStart()
    {
        $userId = Auth::id();
        $today = date('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $rest['attendance_id'] = $todayAttendance->id;
        $rest['rest_start_at'] = date('Y-m-d H:i:s');
        Rest::create($rest);

        $attendance['status_id'] = 3; //3:休憩中
        Attendance::where('user_id', '=', $userId)
                    ->where('attendance_at','LIKE', "%{$today}%")
                    ->update($attendance);
        dd($todayAttendance);
        return redirect()->action(
            [AttendanceController::class, 'attendance']
        );
    }

    public function restEnd()
    {
        $userId = Auth::id();
        $today = date('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $latestRest = Rest::where('attendance_id', $todayAttendance['id'])->latest()->first();
        $rest['rest_end_at'] = date('Y-m-d H:i:s');
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
        $today = date('Y-m-d');
        $todayAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$today}%")
                                        ->first();

        $attendance['status_id'] = 4; //4:退勤済
        $attendance['leaving_at'] = date('Y-m-d H:i:s');

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
            $tmpAttendance = Attendance::where('user_id', '=', $userId)
                                        ->where('attendance_at','LIKE', "%{$tmpDate}%")
                                        ->first();
            $tmpDate = $date->format('Y-m-d H:i:s');
            $attendances[$tmpDate] = [
                'id' =>$tmpAttendance->id ?? null,
                'date' => $tmpDate,
                'attendance_at' => $tmpAttendance->attendance_at ?? null,
                'leaving_at' => $tmpAttendance->leaving_at ?? null,
            ];
        }

        ksort($attendances);
        return view('attendance_list', compact('attendances', 'month'));
    }

    public function detail($id){
        
    }
}
