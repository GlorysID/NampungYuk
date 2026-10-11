<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar — NampungYuk</title>
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-dark.png') }}">
    <meta name="description" content="Bergabunglah dengan komunitas developer NampungYuk. Publikasikan karya codinganmu dan dapatkan feedback nyata.">

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
<body class="bg-[#ffffff] dark:bg-[#000000] text-[#18181b] dark:text-[#fafafa] min-h-screen flex flex-col justify-between selection:bg-[#3291ff]/25 selection:text-[#0070f3] antialiased transition-colors duration-150" x-data>

    <!-- Top mini header for return link & theme toggle -->
    <header class="w-full max-w-7xl mx-auto px-6 py-5 flex items-center justify-between text-xs relative z-20">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[#63636b] dark:text-[#a1a1aa] hover:text-[#18181b] dark:hover:text-white transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Theme toggle button -->
            <button type="button" 
                    @click="$store.theme.toggle()" 
                    class="p-2 rounded-xl text-[#71717a] hover:text-[#18181b] dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition" 
                    title="Ganti Tema">
                <svg x-show="!$store.theme.dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg x-show="$store.theme.dark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>

            <span class="text-[#71717a] hidden sm:inline">Sudah punya akun?</span>
            <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white hover:bg-black/5 dark:hover:bg-white/5 font-semibold transition">
                Masuk
            </a>
        </div>
    </header>

    <!-- MAIN REGISTER GRID (X-STYLE SPLIT) -->
    <main class="w-full max-w-7xl mx-auto px-6 py-6 sm:py-12 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative">
        
        <!-- LEFT COLUMN: Headline & Register Form -->
        <div class="lg:col-span-6 xl:col-span-6 space-y-6 max-w-md lg:max-w-lg z-10">
            
            <!-- Mobile Brand Badge -->
            <div class="flex items-center">
                <x-logo size="md" :href="url('/')" />
            </div>

            <!-- Big bold headline -->
            <div class="space-y-1.5">
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-[#18181b] dark:text-white leading-tight">
                    Bergabunglah <br>hari ini.
                </h1>
                <p class="text-xs sm:text-sm text-[#63636b] dark:text-[#a1a1aa] leading-relaxed">
                    Buat akun dan bagikan project pertamamu ke ribuan developer hari ini.
                </p>
            </div>

            <!-- Social OAuth Buttons (White pill button like X) -->
            <div class="space-y-3 pt-1">
                <a href="{{ route('auth.google') }}" 
                   class="w-full flex items-center justify-center gap-3 py-2.5 px-5 bg-white hover:bg-[#f4f4f5] dark:hover:bg-[#e4e4e7] text-[#18181b] dark:text-[#09090b] rounded-full font-bold text-xs sm:text-sm transition duration-150 active:scale-[0.99] border border-[#e4e4e7] dark:border-transparent shadow-xs dark:shadow-lg">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Daftar dengan Google</span>
                </a>

                <!-- Divider -->
                <div class="relative flex items-center justify-center my-3">
                    <div class="border-t border-[#e4e4e7] dark:border-[#27272a] w-full"></div>
                    <span class="bg-white dark:bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider">atau</span>
                </div>
            </div>

            <!-- Form Register -->
            <form method="POST" action="{{ route('register') }}" class="space-y-3 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="Nama Lengkap" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                        @error('name')
                            <p class="text-rose-500 dark:text-rose-400 font-medium text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <input type="text" 
                               name="username" 
                               value="{{ old('username') }}" 
                               required 
                               placeholder="Username" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                        @error('username')
                            <p class="text-rose-500 dark:text-rose-400 font-medium text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           placeholder="Alamat Email" 
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                    @error('email')
                        <p class="text-rose-500 dark:text-rose-400 font-medium text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="password" 
                               name="password" 
                               required 
                               placeholder="Password (min. 8)" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                        @error('password')
                            <p class="text-rose-500 dark:text-rose-400 font-medium text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <input type="password" 
                               name="password_confirmation" 
                               required 
                               placeholder="Ulangi Password" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black font-bold text-xs sm:text-sm transition duration-150 active:scale-[0.99] shadow-md mt-1">
                    Buat Akun Sekarang
                </button>
            </form>

            <p class="text-[10px] text-[#71717a] dark:text-[#52525b] leading-tight">
                Dengan mendaftar, Anda menyetujui Ketentuan Layanan dan Kebijakan Privasi NampungYuk.
            </p>

        </div>

        <!-- RIGHT COLUMN: Elevated Brand Identity Stage -->
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-6 relative h-[560px] flex-col justify-center items-center select-none overflow-hidden p-8 sm:p-10 rounded-3xl bg-gradient-to-br from-[#f8f9fa] via-[#f1f3f5] to-[#e9ecef] dark:from-[#0c0d14] dark:via-[#08090d] dark:to-[#040406] border border-[#e4e4e7] dark:border-[#1f1f23]">
            
            <!-- Ambient Glow & Tech Grid -->
            <div class="absolute inset-0 bg-[radial-gradient(#0070f3_1px,transparent_1px)] [background-size:20px_20px] opacity-[0.07] dark:opacity-10 pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-[#0070f3]/10 dark:bg-[#0070f3]/20 blur-[110px] pointer-events-none"></div>
            <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full bg-[#00c2cb]/10 dark:bg-[#00c2cb]/15 blur-[90px] pointer-events-none"></div>

            <div class="relative z-10 w-full max-w-sm mx-auto space-y-7 flex flex-col items-center">
                <!-- Top Tag & Headline -->
                <div class="space-y-2 text-center">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[11px] text-[#63636b] dark:text-[#a1a1aa] font-medium backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c2cb] animate-pulse"></span>
                        <span>Showroom Developer Indonesia</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-[#18181b] dark:text-white tracking-tight leading-snug">
                        Tempat karya codinganmu <br><span class="ny-gradient-text">mendapatkan panggung.</span>
                    </h3>
                </div>

                <!-- Center Brand Pedestal & Hero Emblem -->
                <div class="flex flex-col items-center text-center space-y-3.5">
                    <!-- Frosted Glass Pedestal with Ambient Neon Core -->
                    <div class="relative w-40 h-40 sm:w-44 sm:h-44 rounded-3xl bg-white/80 dark:bg-gradient-to-b dark:from-white/[0.08] dark:to-white/[0.02] border border-[#e4e4e7] dark:border-white/10 shadow-[0_20px_50px_rgba(0,112,243,0.12)] dark:shadow-[0_20px_50px_rgba(0,112,243,0.3)] backdrop-blur-xl flex items-center justify-center p-6 group hover:border-[#00c2cb]/40 transition-all duration-500">
                        <div class="absolute inset-2 rounded-2xl bg-radial from-[#0070f3]/15 dark:from-[#0070f3]/25 via-[#00c2cb]/10 to-transparent pointer-events-none"></div>

                        <!-- Hero Brand Logo Icon (Dual: Light & Dark) -->
                        <img src="{{ asset('images/lightmode-icon.png') }}" 
                             alt="NampungYuk Logo" 
                             class="w-full h-full object-contain block dark:hidden drop-shadow-md transform group-hover:scale-105 transition-transform duration-500">
                        <img src="{{ asset('images/darkmode-icon.png') }}" 
                             alt="NampungYuk Logo" 
                             class="w-full h-full object-contain hidden dark:block filter drop-shadow-[0_12px_24px_rgba(0,0,0,0.9)] transform group-hover:scale-105 transition-transform duration-500">

                        <!-- Floating Micro Pills -->
                        <div class="absolute -top-2.5 -right-2.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#00c2cb]/40 text-[#008f97] dark:text-[#00c2cb] text-[10px] font-mono font-bold shadow-md flex items-center gap-1">
                            <span>&lt;/&gt;</span>
                            <span>Code</span>
                        </div>
                        <div class="absolute -bottom-2.5 -left-2.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#3291ff]/40 text-[#0070f3] dark:text-[#3291ff] text-[10px] font-mono font-bold shadow-md flex items-center gap-1">
                            <span>💬</span>
                            <span>Komunitas</span>
                        </div>
                    </div>

                    <div class="space-y-1 max-w-[280px]">
                        <p class="text-base font-extrabold tracking-tight text-[#18181b] dark:text-white">
                            Nampung<span class="text-[#0070f3] dark:text-[#00c2cb]">Yuk</span>
                        </p>
                        <p class="text-xs text-[#63636b] dark:text-[#71717a] leading-relaxed">
                            Satu wadah untuk memamerkan karya kodingan, live demo interaktif, dan apresiasi sesama programmer.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- FOOTER LINKS ALA X -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 border-t border-[#e4e4e7] dark:border-[#18181b] text-[11px] text-[#71717a] flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
        <a href="{{ url('/') }}" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Tentang</a>
        <a href="{{ route('communities.index') }}" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Komunitas</a>
        <a href="{{ route('projects.index') }}" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Showcase</a>
        <a href="#" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Persyaratan</a>
        <a href="#" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Privasi</a>
        <a href="#" class="hover:text-[#18181b] dark:hover:text-white hover:underline">Pengembang</a>
        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" class="hover:text-[#18181b] dark:hover:text-white hover:underline">GitHub</a>
        <span>&copy; {{ date('Y') }} NampungYuk Corp.</span>
    </footer>

</body>
</html>
