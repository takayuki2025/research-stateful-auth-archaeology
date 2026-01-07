<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トップページ</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            @auth
                <img class="company" src="/image_icon/logo.svg" alt="会社名">
            @else
                <a href="/">
                    <img class="company" src="/image_icon/logo.svg" alt="会社名">
                </a>
            @endauth
        </div>
    </header>

    <main>
    @yield('content')
    </main>

</body>

</html>