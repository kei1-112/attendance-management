@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠</h1>

    <div class="main__navigation">
        <a href="{{url('/admin/attendance/list?date=' . $date->copy()->subDay()->format('Y-m-d'))}}" class="navigation__month">←前日</a>
        <div class="navigation__selected--month">
            {{$date->format('Y/m/d')}}
        </div>
        <a href="{{url('/admin/attendance/list?date=' . $date->copy()->addDay()->format('Y-m-d'))}}" class="navigation__month">翌日→</a>
    </div>

    <table class="list__table">
        <tr class="list__row">
            <th class="list__header--first">名前</th>
            <th class="list__header">出勤</th>
            <th class="list__header">退勤</th>
            <th class="list__header">休憩</th>
            <th class="list__header">合計</th>
            <th class="list__header">詳細</th>
        </tr>
        @if($attendances != null)
            @foreach($attendances as $attendance)
            <tr class="list__row">
                <td class="list__data--first">
                    {{ $attendance['user_name'] }}
                </td>
                <td class="list__data">
                    @if($attendance['attendance_at'] != null)
                    {{ \Carbon\Carbon::parse($attendance['attendance_at'])->format('H:i') }}
                    @endif
                </td>
                <td class="list__data">
                    @if($attendance['leaving_at'] != null)
                    {{ \Carbon\Carbon::parse($attendance['leaving_at'])->format('H:i') }}
                    @endif
                </td>
                <td class="list__data">
                    @if($attendance['rests'] != null)
                    {{ \Carbon\Carbon::parse($attendance['rests'])->format('H:i') }}
                    @endif
                </td>
                <td class="list__data">
                    @if($attendance['attendance_sum'] != null)
                    {{ \Carbon\Carbon::parse($attendance['attendance_sum'])->format('H:i') }}
                    @endif
                </td>
                @if($attendance['id'] != null)
                <td class="list__data--detail">
                    <a href="{{url('admin/attendance/' . $attendance['id'] )}}" class="list__link--detail">詳細</a>
                </td>
                @else
                <td class="list__data--detail">詳細</td>
                @endif

            </tr>
            @endforeach
    @endif
    </table>
</div>
@endsection