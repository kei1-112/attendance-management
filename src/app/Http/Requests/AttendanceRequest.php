<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'attendance_at' => ['required'],
            'leaving_at'    => ['required', 'after_or_equal:attendance_at'],
            'remarks'       => ['required'],
        ];

        // rest_start_at の場合
        if ($this->has('rest_start_at')) {
            $val = $this->input('rest_start_at');
            if (is_array($val)) {
                $rules['rest_start_at.*'] = ['sometimes', 'nullable', 'after:attendance_at', 'before:leaving_at'];
            } else {
                $rules['rest_start_at'] = ['sometimes', 'nullable', 'after:attendance_at', 'before:leaving_at'];
            }
        }

        // rest_end_at の場合
        if ($this->has('rest_end_at')) {
            $val = $this->input('rest_end_at');
            if (is_array($val)) {
                $rules['rest_end_at.*'] = ['sometimes', 'nullable', 'before:leaving_at'];
            } else {
                $rules['rest_end_at'] = ['sometimes', 'nullable', 'before:leaving_at'];
            }
        }

        return $rules;
    }

        public function messages()
    {
        return[
            'attendance_at.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'leaving_at.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'leaving_at.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',

            'rest_start_at.after' => '休憩時間が不適切な値です',
            'rest_start_at.*.after' => '休憩時間が不適切な値です',
            'rest_start_at.*.before' => '休憩時間が不適切な値です',
            'rest_start_at.before' => '休憩時間が不適切な値です',

            'rest_end_at.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'rest_end_at.*.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}
