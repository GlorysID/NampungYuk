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
<body class="bg-[#F6F8F7] dark:bg-[#0F1413] text-[#17211F] dark:text-[#F2F5F4] min-h-screen flex flex-col justify-between p-4 sm:p-6 transition-colors duration-150 antialiased selection:bg-[#CCFBF1] selection:text-[#0F766E]"
      x-data>

    <!-- Top Navigation Header (Back Link & Theme Toggle) -->
    <div class="w-full max-w-md mx-auto flex items-center justify-between text-xs py-2">
        <a href="{{ url('/') }}" 
           class="inline-flex items-center gap-1.5 text-[#66736F] dark:text-[#8E9F9B] hover:text-[#0F766E] dark:hover:text-teal-400 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <!-- Quick Theme Toggle -->
        <button @click="$store.theme.toggle()" 
                type="button"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-[#66736F] hover:text-[#17211F] dark:text-[#8E9F9B] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition border border-[#DDE5E2] dark:border-[#24322F]"
                aria-label="Ganti mode tema">
            <svg x-show="!$store.theme.dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.theme.dark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

    <!-- Main Login Card -->
    <div class="w-full max-w-md mx-auto my-auto ny-card p-6 sm:p-8 space-y-6 bg-white dark:bg-[#151D1B] border border-[#DDE5E2] dark:border-[#24322F]">
        
        <!-- Brand Header with Official Logo -->
        <div class="text-center space-y-3">
            <x-logo size="lg" :show-tagline="true" class="justify-center" />
            
            <div class="space-y-1 pt-1">
                <h1 class="text-lg font-bold text-[#17211F] dark:text-[#F2F5F4]">Masuk ke Akun</h1>
                <p class="text-xs text-[#66736F] dark:text-[#8E9F9B]">Masuk untuk mempublikasikan karya project codingan & memberikan apresiasi.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="p-3.5 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-[#0F766E] dark:text-teal-300 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0F766E] dark:text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Google OAuth Button -->
        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white dark:bg-[#151D1B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] text-[#17211F] dark:text-[#F2F5F4] border border-[#DDE5E2] dark:border-[#24322F] rounded-lg font-semibold text-xs transition shadow-2xs active:scale-[0.99]">
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
            <div class="border-t border-[#DDE5E2] dark:border-[#24322F] w-full"></div>
            <span class="bg-white dark:bg-[#151D1B] px-3 text-[11px] font-mono uppercase text-[#66736F] dark:text-[#8E9F9B]">atau</span>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Username or Email -->
            <div class="space-y-1.5">
                <label for="login" class="flex items-center gap-1.5 font-semibold text-[#17211F] dark:text-[#F2F5F4]">
                    <svg class="w-3.5 h-3.5 text-[#66736F] dark:text-[#8E9F9B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                       placeholder="budi_dev atau budi@example.com"
                       class="ny-input text-xs sm:text-sm">
                @error('login')
                    <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="flex items-center gap-1.5 font-semibold text-[#17211F] dark:text-[#F2F5F4]">
                    <svg class="w-3.5 h-3.5 text-[#66736F] dark:text-[#8E9F9B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <div class="flex items-center justify-between text-xs text-[#66736F] dark:text-[#8E9F9B] pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#DDE5E2] dark:border-[#24322F] bg-white dark:bg-[#151D1B] text-[#0F766E] focus:ring-[#0F766E]">
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" 
                    class="btn-primary w-full py-2.5 text-xs sm:text-sm shadow-xs font-bold gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="text-center text-xs text-[#66736F] dark:text-[#8E9F9B] space-y-2 border-t border-[#DDE5E2] dark:border-[#24322F] pt-4">
            <p>Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-[#0F766E] dark:text-teal-400 hover:underline">Daftar sekarang</a></p>
        </div>

    </div>

    <!-- Auth Footer -->
    <div class="w-full max-w-md mx-auto text-center text-[11px] text-[#66736F] dark:text-[#8E9F9B] py-3">
        <span>NampungYuk &copy; {{ date('Y') }} &bull; Platform Komunitas Developer</span>
    </div>

</body>
</html>
