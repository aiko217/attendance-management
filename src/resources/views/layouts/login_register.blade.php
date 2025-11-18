<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/layouts/login_register.css')}}">
    @yield('css')
</head>
<body>
<header class="header">
    <div class="header__inner">
    <img src="{{ asset('images/logo.svg') }}" alt="アプリロゴ" class="img-content" />
    </div>
</header>
<main>
    @yield('content')
</main>

</body>
</html>