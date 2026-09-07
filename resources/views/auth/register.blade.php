<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun — NampungYuk</title>
</head>
<body>
    <h1>Daftar Akun</h1>

    @if (session('status'))
        <p role="alert">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <p>
            <label for="name">Nama Lengkap</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label for="username">Username</label><br>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required>
            @error('username')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label for="email">Email</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" required>
            @error('password')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <label for="password_confirmation">Konfirmasi Password</label><br>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            @error('password_confirmation')<br><small>{{ $message }}</small>@enderror
        </p>

        <p>
            <button type="submit">Daftar</button>
        </p>
    </form>

    <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    <p><a href="{{ url('/') }}">Kembali ke Beranda</a></p>
</body>
</html>
