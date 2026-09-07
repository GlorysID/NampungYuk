<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — NampungYuk</title>
</head>
<body>
    <h1>Masuk</h1>

    @if (session('status'))
        <p role="alert">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <p>
            <label for="login">Email atau Username</label><br>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required>
            @error('login')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" required>
            @error('password')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label>
                <input type="checkbox" name="remember" value="1"> Ingat saya
            </label>
        </p>

        <p>
            <button type="submit">Masuk</button>
        </p>
    </form>

    <p>Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    <p><a href="{{ url('/') }}">Kembali ke Beranda</a></p>
</body>
</html>
