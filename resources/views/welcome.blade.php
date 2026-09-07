<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NampungYuk</title>
</head>
<body>
    <h1>NampungYuk — Berbagi Project Software Engineer</h1>
    <p>NampungYuk adalah platform bagi para software engineer untuk membagikan project, ide, dan pengalaman ngoding mereka. Tunjukkan karya Anda, dapatkan masukan dari komunitas, dan temukan kolaborator untuk project berikutnya.</p>

    @if (session('status'))
        <p role="alert">{{ session('status') }}</p>
    @endif

    @auth
        <p>Halo, {{ auth()->user()->name }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    @endauth

    @guest
        <p><a href="{{ route('login') }}">Masuk</a></p>
        <p><a href="{{ route('register') }}">Daftar Sekarang</a></p>
    @endguest
</body>
</html>
