<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as RequestModel;

class RequestController extends Controller
{
    public function request(AttendanceRequest $request){
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

            $attendance['user_id'] = Auth::id();
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

        $correctRequest['attendance_id'] = $attendanceId;
        $correctRequest['approval_flag'] = 1; //承認待ち
        $correctRequest['requested_at'] = now();
        RequestModel::create($correctRequest);

        return redirect('/stamp_correction_request/list');
    }

    public function list(Request $request){
        $userId = Auth::id();
        $param = $request->tab;
        if($param == null){
            $requests = RequestModel::query()
                                ->where('approval_flag', 1)
                                ->whereHas('attendance.user', fn($q) => $q->where('id', $userId))
                                ->get();
        }else{
            $requests = RequestModel::query()
                                ->where('approval_flag', 0)
                                ->whereHas('attendance.user', fn($q) => $q->where('id', $userId))
                                ->get();
        }
        $user = User::where('id', '=', $userId)
                        ->first();
        return view('request', compact('requests', 'user', 'param'));
    }
}
