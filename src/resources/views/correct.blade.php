@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="main">
    <h1 class="main__title">勤怠詳細</h1>
    <table class="detail__table">
        <tr class="detail__row">
            <th class="detail__header">名前</th>
            <td class="detail__data">{{$attendance->user->name}}</td>
        </tr>
        <tr class="detail__row">
            <th class="detail__header">日付</th>
            <td class="detail__data">{{ \Carbon\Carbon::parse($attendance->attendance_at)->format('Y年　　　　　m月d日') }}</td>
        </tr>
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
        @if( $correctRequest->approval_flag == 1 )
        <button type="button" class="button__submit button__approve" data-id="{{ $attendance_correct_request_id }}">承認</button>
        @else
        <button class="button__submit button__approved">承認済み</button>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.button__approve').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch(`/stamp_correction_request/approve/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(res => res.json())
                .then(result => {
                    if (result.status === "success") {
                        this.textContent = "承認済み";
                        this.disabled = true;
                        this.classList.remove('button__approve');
                        this.classList.add('button__approved');
                    }
                });
            });
        });
    });
</script>
@endsection