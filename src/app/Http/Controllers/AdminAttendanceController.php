<?php

namespace App\Http\Controllers;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\Request as RequestModel;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request){
        $dateString = $request->query('date');
        if($dateString == null){
            $dateString = now()->format('Y-m-d');
        }

        $date = Carbon::createFromFormat('Y-m-d', $dateString);
        $tmpAttendances = Attendance::where('attendance_at', 'LIKE', "%{$dateString}%")
                                    ->get();
        $attendances = null;
        if(!$tmpAttendances->isEmpty()){
            foreach($tmpAttendances as $attendance){
                $attendanceId = $attendance->id;
                $userId = $attendance->user_id;
                $userName = User::where('id', '=', $userId)->value('name');

                //その日の勤怠IDに紐づく休憩時間の計算
                $rests = Rest::where('attendance_id', '=', $attendanceId)
                                ->get();

                $tmpRest = null;
                $attendanceSum = null;

                if(!$rests->isEmpty()){
                    foreach($rests as $rest){
                        $restStart = date('Y-m-d H:i:00', strtotime($rest['rest_start_at']));
                        $restEnd = date('Y-m-d H:i:00', strtotime($rest['rest_end_at']));
                        $restDiff = strtotime($restEnd) - strtotime($restStart);
                        $tmpRest = $tmpRest + $restDiff;
                        }
                }

                //その日の勤怠の合計を計算
                $tmpAttendanceAt = date('Y-m-d H:i:00', strtotime($attendance['attendance_at']));
                $tmpLeavingAt = date('Y-m-d H:i:00', strtotime($attendance['leaving_at']));
                $attendanceDiff = strtotime($tmpLeavingAt) - strtotime($tmpAttendanceAt);
                $attendanceSum = $attendanceDiff - $tmpRest;

                $attendances[$userId] = [
                    'id' =>$attendanceId  ?? null,
                    'user_name' => $userName ?? null,
                    'attendance_at' => $attendance->attendance_at ?? null,
                    'leaving_at' => $attendance->leaving_at ?? null,
                    'rests' => $tmpRest ?? null,
                    'attendance_sum' => $attendanceSum??null,
                ];
            }
        }
        return view('admin_attendance_list', compact('attendances', 'date'));
    }

    public function detail(Request $request, $id){
        if(strpos($id, '-') !== false){
            $date = $id;
            $userId = $request->query('user_id');
            $user = User::where('id', '=', $userId)->first();
            return view('admin_attendance_detail', compact('date', 'user'));
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
        return view('admin_attendance_detail', compact('attendance', 'rests', 'latestRequest', 'date'));
    }

    public function correct(AttendanceRequest $request){
        if($request->attendance_id != 'null'){
            $attendanceId = $request->attendance_id;
            $date = $request->date;
            $attendance_at = $request->attendance_at;
            $leaving_at = $request->leaving_at;

            $attendance['attendance_at'] = $date . " " . $attendance_at;
            $attendance['leaving_at'] = $date . " " . $leaving_at;
            $attendance['remarks'] = $request->remarks;

            Attendance::where('id', '=', $attendanceId)
                        ->update($attendance);

            $rests = Rest::where('attendance_id', '=', $attendanceId)->get();

            if(!$rests->isEmpty()){
                foreach($rests as $index => $rest){
                    $tmpRest['rest_start_at'] = $date . " " . $request->input("rest_start_at.$index");
                    $tmpRest['rest_end_at'] = $date . " " . $request->input("rest_end_at.$index");
                    Rest::where('id', '=', $rest['id'])
                        ->update($tmpRest);
                }

                if(($request->input("rest_start_at")[$index + 1]) != null){
                    $tmpRest['attendance_id'] = $attendanceId;
                    $tmpRest['rest_start_at'] = $date . " " . $request->input("rest_start_at")[$index + 1];
                    $tmpRest['rest_end_at'] = $date . " " . $request->input("rest_end_at")[$index + 1];
                    Rest::create($tmpRest);
                }
            }elseif(($request->input("rest_start_at")) != null){
                $tmpRest['attendance_id'] = $attendanceId;
                $tmpRest['rest_start_at'] = $date . " " . $request->input("rest_start_at");
                $tmpRest['rest_end_at'] = $date . " " . $request->input("rest_end_at");
                Rest::create($tmpRest);
            }
        }else{
            $date = $request->date;
            $attendance_at = $request->attendance_at;
            $leaving_at = $request->leaving_at;
            $rest_start_at = $request->rest_start_at;
            $rest_end_at = $request->rest_end_at;

            $attendance['user_id'] = $request->user_id;
            $attendance['status_id'] = 4;
            $attendance['attendance_at'] = $date . " " . $attendance_at;
            $attendance['leaving_at'] = $date . " " . $leaving_at;
            $attendance['remarks'] = $request->remarks;

            $newAttendance = Attendance::create($attendance);
            $attendanceId = $newAttendance->id;

            if($rest_start_at != null){
                $rest['attendance_id'] = $attendanceId;
                $rest['rest_start_at'] = $date . " " . $rest_start_at;
                $rest['rest_end_at'] = $date . " " . $rest_end_at;
                Rest::create($rest);
            }
        }
        return redirect('admin/attendance/' . $attendanceId);
    }

    public function export(Request $request){
        //勤怠情報の取得
        $id = $request->input('id');
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
            // dd($attendances);
            //CSVのexport
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="attendances.csv"',
            ];

            $columns = ['日付', '出勤', '退勤', '休憩', '合計'];

            $callback = function () use ($attendances, $columns){
                $file = fopen('php://output', 'w');

                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, $columns);
                //データの入力

                foreach ($attendances as $attendance) {
                    $date = Carbon::parse($attendance['date'])->isoFormat('M/D(dd)');
                    $attendance_at = $attendance['attendance_at']
                                    ? Carbon::parse($attendance['attendance_at'])->isoFormat('H:mm')
                                    : null;
                    $leaving_at = $attendance['leaving_at']
                                    ? Carbon::parse($attendance['leaving_at'])->isoFormat('H:mm')
                                    : null;
                    $rests = $attendance['rests']
                                    ? Carbon::parse($attendance['rests'])->isoFormat('H:mm')
                                    : null;
                    $attendance_sum = $attendance['attendance_sum']
                                    ? Carbon::parse($attendance['attendance_sum'])->isoFormat('H:mm')
                                    : null;
                    fputcsv($file, [
                        $date,
                        $attendance_at,
                        $leaving_at,
                        $rests,
                        $attendance_sum,
                    ]);
                }

                fclose($file);
            };

        return response()->stream($callback, 200, $headers);
    }
}