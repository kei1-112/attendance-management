<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            @if(Auth::guard('admin')->check())
            <div class="header__logo">
                <a href="/admin/attendance/list">
                    <img src="{{asset('storage/logo.svg')}}" alt="" class="header__img">
                </a>
            </div>
            <div class="header__buttons">
                <div class="header__button">
                    <a href="/admin/attendance/list" class="header__button--link">勤怠一覧</a>
                </div>
                <div class="header__button">
                    <a href="/admin/staff/list" class="header__button--link">スタッフ一覧</a>
                </div>
                <div class="header__button">
                    <a href="/stamp_correction_request/list" class="header__button--link">申請一覧</a>
                </div>
                <div class="header__button">
                    <form action="/admin/logout" method="post" class="header__form">
                        @csrf
                        <button class="header__button--logout" type="submit">ログアウト</button>
                    </form>
                </div>
            @elseif(Auth::check())
            <div class="header__logo">
                <a href="/attendance">
                    <img src="{{asset('storage/logo.svg')}}" alt="" class="header__img">
                </a>
            </div>
            <div class="header__buttons">
                <div class="header__button">
                    <a href="/attendance" class="header__button--link">勤怠</a>
                </div>
                <div class="header__button">
                    <a href="{{url('/attendance/list?month=' . \Carbon\Carbon::now()->format('Y-m'))}}" class="header__button--link">勤怠一覧</a>
                </div>
                <div class="header__button">
                    <a href="/stamp_correction_request/list" class="header__button--link">申請</a>
                </div>
                <div class="header__button">
                    <form action="/logout" method="post" class="header__form">
                        @csrf
                        <button class="header__button--logout" type="submit">ログアウト</button>
                    </form>
                </div>
            @else
                    <a class="header__button--login" href='/login'>ログイン</a>
            @endif
            </div>
        </div>
    </header>
    <main>
    @yield('content')
    </main>
</body>
</html>