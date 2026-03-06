<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>

    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
            </div>

            @auth
            <nav class="header__nav">
                <a href="{{ route('attendance.index') }}" class="header__link">勤怠</a>
                <a href="{{ route('attendance.list') }}" class="header__link">勤怠一覧</a>
                <a href="{{ route('stamp_correction_request.list') }}" class="header__link">申請</a>

                <form method="POST" action="/logout" class="header__logout-form">
                    @csrf
                    <button type="submit" class="header__link header__logout-button">
                        ログアウト
                    </button>
                </form>
            </nav>
            @endauth
        </div>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>