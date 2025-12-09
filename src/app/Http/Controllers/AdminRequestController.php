<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Attendance;
use App\Models\Rest;

class AdminRequestController extends Controller
{
    public function list(Request $request){
        $param = $request->tab;
        if($param == null){
            $requests = RequestModel::where('approval_flag', '=', 1)
                                        ->get();
        }else{
            $requests = RequestModel::where('approval_flag', '=', 0)
                                        ->get();
        }
        return view('admin_request', compact('requests', 'param'));
    }

    public function detail($attendance_correct_request_id){
        $id = RequestModel::find($attendance_correct_request_id)->attendance_id;
        $correctRequest = RequestModel::where('id', '=', $attendance_correct_request_id)->first();
        $attendance = Attendance::where('id', '=', $id)
                                ->first();
        $rests = Rest::where('attendance_id', '=', $id)
                    ->get();
        return view('correct', compact('attendance', 'rests', 'attendance_correct_request_id', 'correctRequest'));
    }

    public function correct($id){
        RequestModel::where('id', $id)->update([
            'approval_flag' => 0 //承認済み
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '承認済',
        ]);
    }
}