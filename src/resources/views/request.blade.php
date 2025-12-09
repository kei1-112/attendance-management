@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">申請一覧</h1>
    <div class="request__tabs">
        @if($param == null)
        <a href="/stamp_correction_request/list" class="request__tab request__tab--selected">承認待ち</a>
        <a href="/stamp_correction_request/list?tab=approved" class="request__tab">承認済み</a>
        @else
        <a href="/stamp_correction_request/list" class="request__tab">承認待ち</a>
        <a href="/stamp_correction_request/list?tab=approved" class="request__tab request__tab--selected">承認済み</a>
        @endif
    </div>
    <div class="horizon"></div>
    <form action="/stamp_correction_request" method="post">
        @csrf
        <table class="request__table">
            <tr class="request__row">
                <th class="request__header">状態</th>
                <th class="request__header">名前</th>
                <th class="request__header">対象日時</th>
                <th class="request__header">申請理由</th>
                <th class="request__header">申請日時</th>
                <th class="request__header">詳細</th>
            </tr>
            @foreach($requests as $request)
            <tr class="request__row">
                @if($param == null)
                <td class="request__data">承認待ち</td>
                @else
                <td class="request__data">承認済み</td>
                @endif
                <td class="request__data">{{ $user['name'] }}</td>
                <td class="request__data">{{ \Carbon\Carbon::parse($request->attendance->attendance_at)->format('Y/m/d') }}</td>
                <td class="request__data">{{ $request->attendance->remarks }}</td>
                <td class="request__data">{{ \Carbon\Carbon::parse($request->requested_at)->format('Y/m/d') }}</td>
                <td class="request__data">
                    <a href="/attendance/detail/{{ $request->attendance->id }}" class="request__link--detail">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </form>
</div>
@endsection