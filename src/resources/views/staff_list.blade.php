@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">スタッフ一覧</h1>
    <table class="list__table">
        <th class="list__header--first">名前</th>
        <th class="list__header">メールアドレス</th>
        <th class="list__header">月次勤怠</th>
        @foreach($users as $user)
        <tr class="list__row">
            <td class="list__data--first">{{ $user['name'] }}</td>
            <td class="list__data">{{ $user['email'] }}</td>
            <td class="list__data--detail">
                <a href="{{url('/admin/attendance/staff/' . $user['id'] . '?month=' . \Carbon\Carbon::now()->format('Y-m'))}}" class="list__link--detail">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection