@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="main">
    <div class="message">登録していただいたメールアドレスに認証メールを送付しました。</div>
    <div class="message">メール認証を完了してください</div>

    <div class="authentication">
        <a href="http://localhost:8025" class="authentication__link" target="_blank">
            認証はこちらから
        </a>
    </div>

    <div class="resend">
        <form method="POST" action="{{ route('verification.send') }}">
        @csrf
            <input type="hidden" name="email">
            <button type="submit" class="resend__button">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection