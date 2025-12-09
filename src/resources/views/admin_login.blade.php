@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="main__title">管理者ログイン</div>
        <form class="main__forms" action="{{ route('admin.login') }}"   method="post">
        @csrf
            <div class="main__form">
                <div class="main__form--item">メールアドレス</div>
                <div class="main__form--input">
                    <input type="text" class="form__input" name="email" value="{{ old('email') }}">
                </div>
            </div>
            <div class="error">
            @if($errors->has('email'))
            {{$errors->first('email')}}
            @endif
            </div>
            <div class="main__form">
                <div class="main__form--item">パスワード</div>
                <div class="main__form--input">
                    <input type="password" class="form__input" name="password">
                </div>
            </div>
            <div class="error">
            @if($errors->has('password'))
            {{$errors->first('password')}}
            @endif
            </div>
            <button class="main__button" type="submit">管理者ログインする</button>
        </form>
@endsection