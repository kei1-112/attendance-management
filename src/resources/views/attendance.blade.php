@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="main">
    <div class="display">
        @if($todayAttendance == null)
            <div class="display__status">勤務外</div>
        @else
            <div class="display__status">{{$todayAttendance->status->status}}</div>
        @endif

        <div class="display__date">{{ \Carbon\Carbon::now()->format('Y年m月d日') }}({{ now()->isoFormat('ddd') }})</div>
        <div class="display__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

        @if($todayAttendance == null)
        <form action="attendance" method="post">
            @csrf
            <button class="button__submit" type="submit">
                出勤
            </button>
        </form>
        @elseif($todayAttendance->status->id == 1)
        <form action="attendance" method="post">
            @csrf
            <button class="button__submit" type="submit">
                出勤
            </button>
        </form>
        @elseif($todayAttendance->status->id == 2)
        <div class="display__buttons">
            <form action="leave" method="post">
                @csrf
                <button class="button__submit--leave" type="submit">
                    退勤
                </button>
            </form>
            <form action="rest_start" method="post">
                @csrf
                <button class="button__submit--rest" type="submit">
                    休憩入
                </button>
            </form>
        </div>
        @elseif($todayAttendance->status->id == 3)
        <form action="rest_end" method="post">
            @csrf
            <button class="button__submit--rest" type="submit">
                休憩戻
            </button>
        </form>
        @else
        <div class="display__message">お疲れ様でした。</div>
        @endif
    </div>
</div>
@endsection