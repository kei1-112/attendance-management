@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">勤怠一覧</h1>

    <div class="main__navigation">
        <a href="{{url('/attendance_list?month=' . $month->copy()->subMonth()->format('Y-m'))}}" class="navigation__month">←前月</a>
        <div class="navigation__selected-month">
            {{$month->format('Y/m')}}
        </div>
        <a href="{{url('/attendance_list?month=' . $month->copy()->addMonth()->format('Y-m'))}}" class="navigation__month">翌月→</a>
    </div>

    <table class="list__table">
        <tr class="list__row">
            <th class="list__header--first">日付</th>
            <th class="list__header">出勤</th>
            <th class="list__header">退勤</th>
            <th class="list__header">休憩</th>
            <th class="list__header">合計</th>
            <th class="list__header">詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="list__row">
            <td class="list__data--first">
                {{ \Carbon\Carbon::parse($attendance['date'])->format('m/d') }}
                ({{ \Carbon\Carbon::parse($attendance['date'])->isoFormat('ddd') }})
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
            <td class="list__data"></td>
            <td class="list__data"></td>
            @if($attendance['id'] != null)
            <td class="list__data--detail">
                <a href="{{url('attendance/detail/' . $attendance['id'] )}}" class="list__link--detail">詳細</a>
            </td>
            @else
            <td class="list__data--detail">詳細</td>
            @endif

        </tr>
        @endforeach
    </table>
</div>
@endsection