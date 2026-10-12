<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — NampungYuk</title>
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-dark.png') }}">
    <meta name="description" content="Masuk ke akun NampungYuk untuk membagikan project codingan dan berdiskusi dengan sesama developer.">

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Fonts: Plus Jakarta Sans + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f4f5] dark:bg-[#060709] text-[#18181b] dark:text-[#fafafa] min-h-screen flex items-center justify-center p-3 sm:p-5 relative antialiased selection:bg-[#3291ff]/25 selection:text-[#0070f3]"
      x-data="{ showPassword: false }">

    <!-- Ambient Glow Backdrop -->
    <div class="fixed inset-0 bg-black/60 dark:bg-black/85 backdrop-blur-md pointer-events-none"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-[#0070f3]/10 dark:bg-[#0070f3]/15 blur-[120px] pointer-events-none"></div>

    <!-- Centered Modal Box (Identical size & layout to Auth Modal) -->
    <div class="relative w-full max-w-md lg:max-w-3xl bg-white dark:bg-[#000000] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] grid grid-cols-1 lg:grid-cols-2 text-[#18181b] dark:text-[#fafafa] z-10">

        <!-- Close Button (X-style rounded button returning to Home) -->
        <a href="{{ url('/') }}" 
           class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[#63636b] hover:text-[#18181b] dark:text-[#a1a1aa] dark:hover:text-white flex items-center justify-center transition border border-black/10 dark:border-white/10 cursor-pointer"
           title="Kembali ke Beranda"
           aria-label="Tutup modal">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>

        <!-- LEFT COLUMN: Login Form -->
        <div class="p-6 sm:p-8 flex flex-col justify-between space-y-4 min-h-[510px]">
            
            <div>
                <!-- Brand header badge -->
                <div class="mb-3.5 flex items-center justify-between">
                    <x-logo size="sm" :href="url('/')" />
                    <!-- Tab Link to Register -->
                    <a href="{{ route('register') }}" class="mr-10 lg:mr-0 text-xs font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                        Daftar Akun &rarr;
                    </a>
                </div>

                <!-- Headline -->
                <div class="space-y-1 mb-4">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#18181b] dark:text-white">
                        Sedang tren saat ini.
                    </h1>
                    <p class="text-xs text-[#63636b] dark:text-[#a1a1aa] leading-relaxed line-clamp-2">
                        Masuk untuk memamerkan karya, bookmark project, dan berdiskusi dengan sesama developer.
                    </p>
                </div>

                <!-- Status alert if redirected (e.g., after registration) -->
                @if (session('status'))
                    <div class="mb-3 p-3 rounded-2xl bg-[#0070f3]/10 dark:bg-[#0070f3]/15 border border-[#0070f3]/25 dark:border-[#3291ff]/30 text-[#0070f3] dark:text-[#47a8ff] text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#0070f3] dark:text-[#3291ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Social OAuth Button: Google -->
                <div class="space-y-3">
                    <a href="{{ route('auth.google') }}" 
                       class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white hover:bg-[#f4f4f5] dark:hover:bg-[#e4e4e7] text-[#18181b] dark:text-[#09090b] rounded-full font-bold text-xs transition duration-150 active:scale-[0.99] border border-[#e4e4e7] dark:border-transparent shadow-xs dark:shadow-sm">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Lanjutkan dengan Google</span>
                    </a>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center my-3.5">
                        <div class="border-t border-[#e4e4e7] dark:border-[#27272a] w-full"></div>
                        <span class="bg-white dark:bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider">
                            atau masuk manual
                        </span>
                    </div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="login" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Email atau Username</label>
                        <input type="text" 
                               id="login" 
                               name="login" 
                               value="{{ old('login') }}" 
                               required 
                               autofocus
                               placeholder="dev@nampungyuk.id atau username" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] focus:ring-1 focus:ring-[#0070f3] dark:focus:ring-[#3291ff] outline-none transition">
                        @error('login')
                            <p class="text-rose-500 dark:text-rose-400 font-medium text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa]">Password</label>
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] focus:ring-1 focus:ring-[#0070f3] dark:focus:ring-[#3291ff] outline-none transition">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-[#18181b] dark:hover:text-white text-xs cursor-pointer">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-500 dark:text-rose-400 font-medium text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center text-[#63636b] dark:text-[#a1a1aa] text-xs pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" class="rounded border-[#e4e4e7] dark:border-[#27272a] bg-[#f8f9fa] dark:bg-[#09090b] text-[#0070f3] focus:ring-[#0070f3]">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black rounded-full font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm cursor-pointer">
                        Masuk
                    </button>
                </form>
            </div>

            <!-- Footer Switcher -->
            <div class="pt-3 border-t border-[#e4e4e7] dark:border-[#27272a] flex items-center justify-between text-xs">
                <span class="text-[#71717a]">Belum punya akun?</span>
                <a href="{{ route('register') }}" 
                   class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline cursor-pointer">
                    Daftar Akun Baru
                </a>
            </div>

        </div>

        <!-- RIGHT COLUMN: Brand Showcase Stage (ala X modal) -->
        <div class="hidden lg:flex relative flex-col justify-center items-center select-none overflow-hidden p-8 bg-gradient-to-br from-[#f8f9fa] via-[#f1f3f5] to-[#e9ecef] dark:from-[#0c0d14] dark:via-[#08090d] dark:to-[#040406] border-l border-[#e4e4e7] dark:border-[#1f1f23]">
            <!-- Ambient Glow & Tech Grid -->
            <div class="absolute inset-0 bg-[radial-gradient(#0070f3_1px,transparent_1px)] [background-size:16px_16px] opacity-[0.08] dark:opacity-10 pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-[#0070f3]/10 dark:bg-[#0070f3]/25 blur-[90px] pointer-events-none"></div>

            <div class="relative z-10 w-full max-w-xs mx-auto space-y-6 flex flex-col items-center text-center">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[10px] text-[#63636b] dark:text-[#a1a1aa] font-medium backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00c2cb] animate-pulse"></span>
                    <span>Showroom Developer Indonesia</span>
                </div>

                <!-- Pedestal with Logo -->
                <div class="relative w-36 h-36 rounded-3xl bg-white/80 dark:bg-gradient-to-b dark:from-white/[0.08] dark:to-white/[0.02] border border-[#e4e4e7] dark:border-white/10 shadow-[0_20px_50px_rgba(0,112,243,0.12)] dark:shadow-[0_20px_50px_rgba(0,112,243,0.3)] backdrop-blur-xl flex items-center justify-center p-5 group hover:border-[#00c2cb]/40 transition-all duration-500">
                    <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk Logo" class="w-full h-full object-contain block dark:hidden drop-shadow-md">
                    <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk Logo" class="w-full h-full object-contain hidden dark:block filter drop-shadow-[0_12px_24px_rgba(0,0,0,0.9)]">

                    <!-- Floating Micro Pills -->
                    <div class="absolute -top-2 -right-2 px-2 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#00c2cb]/40 text-[#008f97] dark:text-[#00c2cb] text-[9px] font-mono font-bold shadow-xs">
                        &lt;/&gt; Code
                    </div>
                    <div class="absolute -bottom-2 -left-2 px-2 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#3291ff]/40 text-[#0070f3] dark:text-[#3291ff] text-[9px] font-mono font-bold shadow-xs">
                        💬 Komunitas
                    </div>
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-extrabold tracking-tight text-[#18181b] dark:text-white">
                        Nampung<span class="text-[#0070f3] dark:text-[#00c2cb]">Yuk</span>
                    </p>
                    <p class="text-[11px] text-[#63636b] dark:text-[#71717a] leading-relaxed">
                        Satu wadah untuk memamerkan karya kodingan, live demo interaktif, dan apresiasi sesama programmer.
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
