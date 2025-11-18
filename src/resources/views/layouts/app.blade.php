<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
    <div class="header__inner">
    <img src="{{ asset('images/logo.svg') }}" alt="アプリロゴ" class="img-content" />
        
        <nav class="header__nav">
          <ul class="header__list">
          @auth
            <li>
            <a class="attendance__button-submit" href="/attendance">勤怠</a>
            </li>
            <li>
            <a class="list__button-submit" href="/attendance/list">勤怠一覧</a>
            </li>
            <li>
            <a class="request__button-submit" href="{{ route('stamp_correction_request.list') }}">申請</a>
            </li>
            <li>
            <form action="{{ route('logout') }}" class="header__form" method="post">
            @csrf
            <button type="submit" class="logout__button-submit">ログアウト</button>
            </form>
            </li>
         @endauth
         </ul>
        </nav>
    </div>
    </header>
    @yield('content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('scripts')
</body>
</html>