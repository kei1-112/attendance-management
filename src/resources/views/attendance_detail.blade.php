@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">勤怠詳細</h1>
    @if($date != null)
        <form action="/stamp_correction_request" method="post">
            @csrf
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}">
            <input type="hidden" name="attendance_id" value=null>

            <table class="detail__table">
                <tr class="detail__row">
                    <th class="detail__header">名前</th>
                    <td class="detail__data">{{$name}}</td>
                </tr>
                <tr class="detail__row">
                    <th class="detail__header">日付</th>
                    <td class="detail__data">{{ \Carbon\Carbon::parse($date)->format('Y年　　　　　m月d日') }}</td>
                </tr>
                <tr class="detail__row">
                    <th class="detail__header">出勤・退勤</th>
                    <td class="detail__data">
                        <input type="text" class="input__field" name="attendance_at"
                                value="{{ old('attendance_at') }}">
                        　　~　　
                        <input type="text" class="input__field" name="leaving_at"
                                value="{{ old('leaving_at') }}">
                        @if($errors->has('leaving_at'))
                        <br>
                        <div class="error">
                        {{$errors->first('leaving_at')}}
                        </div>
                        @endif
                    </td>
                </tr>
                <tr class="detail__row">
                    <th class="detail__header">休憩</th>
                    <td class="detail__data">
                        <input type="text" class="input__field"  name="rest_start_at" value="{{ old('rest_start_at') }}">
                        　　~　　
                        <input type="text" class="input__field"  name="rest_end_at" value="{{ old('rest_end_at') }}">
                        @if($errors->has('rest_start_at'))
                        <br>
                        <div class="error">
                            {{$errors->first('rest_start_at')}}
                        </div>
                        @endif
                        @if($errors->has('rest_end_at'))
                        <br>
                        <div class="error">
                            {{$errors->first('rest_end_at')}}
                        </div>
                        @endif
                    </td>
                </tr>
                <tr class="detail__row">
                    <th class="detail__header">備考</th>
                    <td class="detail__data">
                        <input type="text" class="input__field--remarks" name="remarks"
                                value="{{ old('remarks') }}">
                        @if($errors->has('remarks'))
                        <br>
                        <div class="error">
                        {{$errors->first('remarks')}}
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
            <div class="detail__button">
                <button class="button__submit">修正</button>
            </div>
        </form>
    @else
        <form action="/stamp_correction_request" method="post">
            @csrf
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($attendance->attendance_at)->format('Y-m-d') }}">
            <input type="hidden" name="attendance_id" value="{{ ($attendance->id) }}">
            <table class="detail__table">
                <tr class="detail__row">
                    <th class="detail__header">名前</th>
                    <td class="detail__data">{{$attendance->user->name}}</td>
                </tr>
                <tr class="detail__row">
                    <th class="detail__header">日付</th>
                    <td class="detail__data">{{ \Carbon\Carbon::parse($attendance->attendance_at)->format('Y年　　　　　m月d日') }}</td>
                </tr>
                @if($latestRequest == null || $latestRequest['approval_flag'] == 0)
                <tr class="detail__row">
                    <th class="detail__header">出勤・退勤</th>
                    <td class="detail__data">
                        <input type="text" class="input__field" name="attendance_at"
                                value="{{ old('attendance_at', \Carbon\Carbon::parse($attendance->attendance_at)->format('H:i')) }}">
                        　　~　　
                        <input type="text" class="input__field" name="leaving_at"
                                value="{{ old('leaving_at', \Carbon\Carbon::parse($attendance->leaving_at)->format('H:i')) }}">
                        @if($errors->has('leaving_at'))
                        <br>
                        <div class="error">
                        {{$errors->first('leaving_at')}}
                        </div>
                        @endif
                    </td>
                </tr>
                @if($rests->isEmpty())
                <tr class="detail__row">
                    <th class="detail__header">休憩</th>
                    <td class="detail__data">
                        <input type="text" class="input__field"  name="rest_start_at" value="{{ old('rest_start_at') }}">
                        　　~　　
                        <input type="text" class="input__field"  name="rest_end_at" value="{{ old('rest_end_at') }}">
                        @if($errors->has('rest_start_at'))
                        <br>
                        <div class="error">
                            {{$errors->first('rest_start_at')}}
                        </div>
                        @endif
                        @if($errors->has('rest_end_at'))
                        <br>
                        <div class="error">
                            {{$errors->first('rest_end_at')}}
                        </div>
                        @endif
                    </td>
                </tr>
                @else
                    @foreach($rests as $index => $rest)
                        @if($loop->first)
                        <tr class="detail__row">
                            <th class="detail__header">休憩</th>
                            <td class="detail__data">
                                <input type="text" class="input__field"  name="rest_start_at[{{ $index }}]"
                                        value= "{{ old( 'rest_start_at.' . $index, \Carbon\Carbon::parse($rest['rest_start_at'])->format('H:i')) }}" >
                                　　~　　
                                <input type="text" class="input__field"  name="rest_end_at[{{ $index }}]"
                                        value= "{{ old('rest_end_at.' . $index, \Carbon\Carbon::parse($rest['rest_end_at'])->format('H:i')) }}" >
                                @if($errors->has('rest_start_at.' . $index))
                                <br>
                                <div class="error">
                                    {{$errors->first('rest_start_at.' . $index)}}
                                </div>
                                @endif
                                @if($errors->has('rest_end_at.' . $index))
                                <br>
                                <div class="error">
                                    {{$errors->first('rest_end_at.' . $index)}}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @else
                        <tr class="detail__row">
                            <th class="detail__header">休憩{{ $loop->iteration }}</th>
                            <td class="detail__data">
                            <input type="text" class="input__field"  name="rest_start_at[{{ $index }}]"
                                    value= "{{ old('rest_start_at.' . $index, \Carbon\Carbon::parse($rest['rest_start_at'])->format('H:i')) }}" >
                                　　~　　
                            <input type="text" class="input__field"  name="rest_end_at[{{ $index }}]"
                                    value= "{{ old('rest_end_at.' . $index, \Carbon\Carbon::parse($rest['rest_end_at'])->format('H:i')) }}" >
                            @if($errors->has('rest_start_at.' . $index))
                            <br>
                            <div class="error">
                                {{$errors->first('rest_start_at.' . $index)}}
                            </div>
                            @endif
                            @if($errors->has('rest_end_at.' . $index))
                            <br>
                            <div class="error">
                                {{$errors->first('rest_end_at.' . $index)}}
                            </div>
                            @endif
                            </td>
                        </tr>
                        @endif
                        @if($loop->last)
                        <?php
                            $array_num = $loop->iteration;
                            $rest_num = $loop->iteration + 1
                        ?>
                        <tr class="detail__row">
                            <th class="detail__header">休憩{{ $rest_num }}</th>
                            <td class="detail__data">
                                <input type="text" class="input__field" name="rest_start_at[{{ $array_num }}]" value="{{ old('rest_start_at.' .$array_num) }}">
                                　　~　　
                                <input type="text" class="input__field" name="rest_end_at[{{ $array_num }}]" value="{{ old('rest_end_at.' .$array_num) }}">
                                @if($errors->has('rest_start_at.' . $array_num))
                                <br>
                                <div class="error">
                                    {{$errors->first('rest_start_at.' . $array_num)}}
                                </div>
                                @endif
                                @if($errors->has('rest_end_at.' . $array_num))
                                <br>
                                <div class="error">
                                    {{$errors->first('rest_end_at.' . $array_num)}}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @endif
                <tr class="detail__row">
                    <th class="detail__header">備考</th>
                    <td class="detail__data">
                        <input type="text" class="input__field--remarks" name="remarks"
                                value="{{ old('remarks', $attendance['remarks']) }}">
                        @if($errors->has('remarks'))
                        <br>
                        <div class="error">
                        {{$errors->first('remarks')}}
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
            <div class="detail__button">
                <button class="button__submit">修正</button>
            </div>
            @else
                <tr class="detail__row">
                    <th class="detail__header">出勤・退勤</th>
                    <td class="detail__data">
                        {{ \Carbon\Carbon::parse($attendance->attendance_at)->format('H:i') }}
                        　　~　　
                        {{ \Carbon\Carbon::parse($attendance->leaving_at)->format('H:i') }}
                    </td>
                </tr>
                @if($rests->isEmpty())
                <tr class="detail__row">
                </tr>
                @else
                    @foreach($rests as $index => $rest)
                        @if($loop->first)
                        <tr class="detail__row">
                            <th class="detail__header">休憩</th>
                            <td class="detail__data">
                                {{ \Carbon\Carbon::parse($rest['rest_start_at'])->format('H:i') }}
                                　　~　　
                                {{\Carbon\Carbon::parse($rest['rest_end_at'])->format('H:i') }}
                            </td>
                        </tr>
                        @else
                        <tr class="detail__row">
                            <th class="detail__header">休憩{{ $loop->iteration }}</th>
                                <td class="detail__data">
                                    {{ \Carbon\Carbon::parse($rest['rest_start_at'])->format('H:i') }}
                                    　　~　　
                                    {{\Carbon\Carbon::parse($rest['rest_end_at'])->format('H:i') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif
                <tr class="detail__row">
                    <th class="detail__header">備考</th>
                    <td class="detail__data">
                        {{ $attendance['remarks'] }}
                    </td>
                </tr>
            </table>
            <div class="detail__button">
                <div class="detail__message">*承認待ちのため編集できません</div>
            </div>
            @endif
        </form>
    @endif
</div>
@endsection