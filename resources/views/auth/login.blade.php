<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — NampungYuk</title>
    <meta name="description" content="Masuk ke akun NampungYuk untuk membagikan project codingan dan berdiskusi dengan sesama developer.">

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
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#ffffff] dark:bg-[#000000] text-[#000000] dark:text-[#fafafa] h-dvh overflow-hidden grid grid-rows-[auto_1fr_auto] gap-2 p-4 sm:p-5 transition-colors duration-150 antialiased selection:bg-[#3291ff]/25 selection:text-[#0070f3]"
      x-data>

    <!-- Top Navigation Header (Back Link & Theme Toggle) -->
    <div class="w-full max-w-md mx-auto flex items-center justify-between text-xs">
        <a href="{{ url('/') }}" 
           class="inline-flex items-center gap-1.5 text-[#666666] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <!-- Quick Theme Toggle -->
        <button @click="$store.theme.toggle()" 
                type="button"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-[#666666] hover:text-[#000000] dark:text-[#a0a0a0] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition border border-[#e2e2e2] dark:border-[#1f1f1f]"
                aria-label="Ganti mode tema">
            <svg x-show="!$store.theme.dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.theme.dark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

    <!-- Main Login Card (centered, fills remaining viewport height) -->
    <div class="w-full max-w-md mx-auto min-h-0 flex items-center justify-center">
        <div class="w-full ny-card px-6 py-6 sm:px-7 sm:py-7 space-y-5 bg-white dark:bg-[#0a0a0a] border border-[#e2e2e2] dark:border-[#1f1f1f] max-h-full overflow-y-auto">
        
        <!-- Brand Header with Official Logo -->
        <div class="text-center space-y-2">
            <x-logo size="lg" :show-tagline="true" class="justify-center" />
            
            <div class="space-y-0.5">
                <h1 class="text-base font-bold text-[#000000] dark:text-[#fafafa]">Masuk ke Akun</h1>
                <p class="text-[11px] text-[#666666] dark:text-[#a0a0a0]">Masuk untuk mempublikasikan karya project codingan & memberikan apresiasi.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="p-3 rounded-lg bg-[#e8f2ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 dark:border-[#3291ff]/30 text-[#0070f3] dark:text-[#47a8ff] text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0070f3] dark:text-[#3291ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Google OAuth Button -->
        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white dark:bg-[#0a0a0a] hover:bg-[#f5f5f5] dark:hover:bg-[#111111] text-[#000000] dark:text-[#fafafa] border border-[#e2e2e2] dark:border-[#1f1f1f] rounded-lg font-semibold text-xs transition active:scale-[0.99]">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2c0 2.8.7 5.5 1.9 7.9l3.7-2.9c-.2-.7-.4-1.5-.4-2.3z"/>
                <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/>
            </svg>
            <span>Lanjutkan dengan Google</span>
        </a>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-[#e2e2e2] dark:border-[#1f1f1f] w-full"></div>
            <span class="bg-white dark:bg-[#0a0a0a] px-3 text-[11px] font-mono uppercase text-[#666666] dark:text-[#a0a0a0]">atau</span>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Username or Email -->
            <div class="space-y-1.5">
                <label for="login" class="flex items-center gap-1.5 font-semibold text-[#000000] dark:text-[#fafafa]">
                    <svg class="w-3.5 h-3.5 text-[#666666] dark:text-[#a0a0a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Email atau Username</span>
                </label>
                <input type="text" 
                       id="login" 
                       name="login" 
                       value="{{ old('login') }}" 
                       required 
                       autofocus
                       placeholder="nama@email.com atau username"
                       class="ny-input text-xs sm:text-sm">
                @error('login')
                    <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="flex items-center gap-1.5 font-semibold text-[#000000] dark:text-[#fafafa]">
                    <svg class="w-3.5 h-3.5 text-[#666666] dark:text-[#a0a0a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Password</span>
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="••••••••"
                       class="ny-input text-xs sm:text-sm">
                @error('password')
                    <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-[#666666] dark:text-[#a0a0a0] pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#e2e2e2] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a] text-[#0070f3] focus:ring-[#0070f3]">
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" 
                    class="btn-primary w-full py-2.5 text-xs sm:text-sm font-bold gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="text-center text-xs text-[#666666] dark:text-[#a0a0a0] space-y-2 border-t border-[#e2e2e2] dark:border-[#1f1f1f] pt-4">
            <p>Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">Daftar sekarang</a></p>
        </div>

        </div>
    </div>

    <!-- Auth Footer -->
    <div class="w-full max-w-md mx-auto text-center text-[11px] text-[#666666] dark:text-[#a0a0a0]">
        <span>NampungYuk &copy; {{ date('Y') }} &bull; Platform Komunitas Developer</span>
    </div>

</body>
</html>
