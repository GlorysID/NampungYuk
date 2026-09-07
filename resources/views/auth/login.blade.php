<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — NampungYuk</title>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Fonts: Plus Jakarta Sans + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-neutral-100 dark:bg-[#0a0a0a] text-neutral-900 dark:text-neutral-100 min-h-screen flex items-center justify-center p-4 selection:bg-orange-500 selection:text-white transition-colors duration-150">

    <div class="w-full max-w-md bg-white dark:bg-[#121212] border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-xl p-6 sm:p-8 space-y-6">
        
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-bold shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                    Nampung<span class="text-orange-500">Yuk</span>
                </span>
            </a>
            <h1 class="text-lg font-bold text-neutral-900 dark:text-white">Masuk</h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Masuk ke akunmu untuk membagikan project dan memberikan vote.</p>
        </div>

        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-neutral-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Google OAuth Button -->
        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white dark:bg-[#181818] hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-700 rounded-xl font-semibold text-xs transition shadow-xs active:scale-98">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2c0 2.8.7 5.5 1.9 7.9l3.7-2.9c-.2-.7-.4-1.5-.4-2.3z"/>
                <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/>
            </svg>
            <span>Masuk dengan Google</span>
        </a>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-neutral-200 dark:border-neutral-800 w-full"></div>
            <span class="bg-white dark:bg-[#121212] px-3 text-[11px] text-neutral-400 uppercase tracking-wider font-medium">atau</span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="login" class="flex items-center gap-1.5 font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Email atau Username</span>
                </label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                       placeholder="budi_dev atau budi@example.com"
                       class="w-full px-4 py-2.5 bg-neutral-50 dark:bg-[#181818] border border-neutral-300 dark:border-neutral-700 rounded-xl focus:border-orange-500 focus:ring-1 focus:ring-orange-500/20 text-neutral-900 dark:text-white placeholder-neutral-400">
                @error('login')
                    <p class="text-rose-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="flex items-center gap-1.5 font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Password</span>
                </label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 bg-neutral-50 dark:bg-[#181818] border border-neutral-300 dark:border-neutral-700 rounded-xl focus:border-orange-500 focus:ring-1 focus:ring-orange-500/20 text-neutral-900 dark:text-white placeholder-neutral-400">
                @error('password')
                    <p class="text-rose-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="rounded border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-orange-500 focus:ring-orange-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full flex items-center justify-center gap-2 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-xl shadow-xs active:scale-98 transition">
                <span>Masuk</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="text-center text-xs text-neutral-500 dark:text-neutral-400 space-y-2 border-t border-neutral-100 dark:border-neutral-800 pt-4">
            <p>Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-orange-500 hover:underline">Daftar sekarang</a></p>
            <p>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </p>
        </div>

    </div>

</body>
</html>
