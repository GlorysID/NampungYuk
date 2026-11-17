<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · @yield('title') — NampungYuk</title>
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--bg-app)] text-[var(--primary)] min-h-screen flex items-center justify-center p-4 antialiased selection:bg-[var(--primary)] selection:text-[var(--primary-text)]">
    <main class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-8 max-w-md w-full text-center space-y-4">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)] font-mono text-xs font-bold tracking-widest text-[var(--text-muted)]">
                ERROR @yield('code')
            </span>
        </div>

        <h1 class="text-xl font-bold text-[var(--primary)] tracking-tight">
            @yield('title')
        </h1>

        <p class="text-sm text-[var(--text-muted)] leading-relaxed">
            @yield('message')
        </p>

        <div class="pt-2">
            <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : url('/') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] text-xs font-bold hover:opacity-90 transition">
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </main>
</body>
</html>
