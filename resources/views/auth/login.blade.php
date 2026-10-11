<!DOCTYPE html>
<html lang="id" class="dark">
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
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#000000] text-[#fafafa] min-h-screen flex flex-col justify-between selection:bg-[#3291ff]/25 selection:text-[#0070f3] antialiased">

    <!-- Top mini header for return link -->
    <header class="w-full max-w-7xl mx-auto px-6 py-5 flex items-center justify-between text-xs relative z-20">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[#a1a1aa] hover:text-white transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-[#71717a] hidden sm:inline">Belum punya akun?</span>
            <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-full border border-[#27272a] text-white hover:bg-white/5 font-semibold transition">
                Daftar
            </a>
        </div>
    </header>

    <!-- MAIN HERO / LOGIN GRID (X-STYLE SPLIT) -->
    <main class="w-full max-w-7xl mx-auto px-6 py-6 sm:py-12 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative">
        
        <!-- LEFT COLUMN: Headline & Auth Buttons (Inspired by X screenshot) -->
        <div class="lg:col-span-6 xl:col-span-6 space-y-7 max-w-md lg:max-w-lg z-10">
            
            <!-- Mobile Brand Badge -->
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl ny-gradient-bg text-white flex items-center justify-center font-bold text-base shadow-lg shadow-[#0070f3]/20">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10.5 4.6 5.4A1.5 1.5 0 0 1 6 4.35h12a1.5 1.5 0 0 1 1.4 1.05L21 10.5" />
                        <path d="M3 10.5h4.5l1.2 2.1h6.6l1.2-2.1H21v6.9a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4z" />
                        <path d="M12 1.8v4.2" />
                        <path d="M10.4 4.4 12 6l1.6-1.6" />
                    </svg>
                </div>
                <span class="text-xl font-black tracking-tight text-white">Nampung<span class="ny-gradient-text">Yuk</span></span>
            </div>

            <!-- Big bold headline ala X: "Sedang tren saat ini." -->
            <div class="space-y-2">
                <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white leading-[1.1]">
                    Sedang tren <br>saat ini.
                </h1>
                <p class="text-sm text-[#a1a1aa] leading-relaxed">
                    Gabung NampungYuk sekarang. Pamerkan project codinganmu, pelajari arsitektur dev lain, dan bangun koneksi.
                </p>
            </div>

            @if (session('status'))
                <div class="p-3.5 rounded-xl bg-[#0070f3]/15 border border-[#3291ff]/30 text-[#47a8ff] text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#3291ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Social OAuth Buttons (White pill button like X) -->
            <div class="space-y-3 pt-1">
                <a href="{{ route('auth.google') }}" 
                   class="w-full flex items-center justify-center gap-3 py-3 px-5 bg-white hover:bg-[#e4e4e7] text-black rounded-full font-bold text-xs sm:text-sm transition duration-150 active:scale-[0.99] shadow-lg">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                        <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2c0 2.8.7 5.5 1.9 7.9l3.7-2.9c-.2-.7-.4-1.5-.4-2.3z"/>
                        <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/>
                    </svg>
                    <span>Lanjutkan dengan Google</span>
                </a>

                <!-- Divider -->
                <div class="relative flex items-center justify-center my-4">
                    <div class="border-t border-[#27272a] w-full"></div>
                    <span class="bg-[#000000] px-3 text-[11px] font-mono uppercase text-[#71717a] tracking-wider">atau</span>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <input type="text" 
                           id="login" 
                           name="login" 
                           value="{{ old('login') }}" 
                           required 
                           autofocus
                           placeholder="Email atau nama pengguna"
                           class="w-full px-4 py-3 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs sm:text-sm placeholder-[#52525b] focus:border-[#3291ff] focus:ring-1 focus:ring-[#3291ff] outline-none transition">
                    @error('login')
                        <p class="text-rose-400 font-medium text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           placeholder="Password"
                           class="w-full px-4 py-3 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs sm:text-sm placeholder-[#52525b] focus:border-[#3291ff] focus:ring-1 focus:ring-[#3291ff] outline-none transition">
                    @error('password')
                        <p class="text-rose-400 font-medium text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-[#a1a1aa] text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="rounded border-[#27272a] bg-[#09090b] text-[#0070f3] focus:ring-[#0070f3]">
                        <span>Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="font-semibold text-[#3291ff] hover:underline">Lupa password?</a>
                </div>

                <button type="submit" 
                        class="w-full py-3 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white font-bold text-xs sm:text-sm border border-[#3f3f46] transition duration-150 active:scale-[0.99] shadow-sm">
                    Lanjutkan
                </button>
            </form>

            <p class="text-[11px] text-[#52525b] leading-tight">
                Dengan melanjutkan, Anda menyetujui <a href="#" class="underline hover:text-[#a1a1aa]">Syarat Layanan</a>, <a href="#" class="underline hover:text-[#a1a1aa]">Kebijakan Privasi</a>, dan <a href="#" class="underline hover:text-[#a1a1aa]">Penggunaan Cookie</a> kami.
            </p>

        </div>

        <!-- RIGHT COLUMN: Giant NampungYuk Outline Watermark (X Geometric Motif) -->
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-6 relative h-[540px] items-center justify-center select-none overflow-hidden">
            
            <!-- Glow effect -->
            <div class="absolute w-96 h-96 rounded-full bg-[#0070f3]/10 blur-[120px] pointer-events-none"></div>

            <!-- Giant Geometric Motif Icon of NampungYuk (Tray & Drop Arrow) -->
            <svg class="w-full max-w-[500px] h-auto text-[#27272a] hover:text-[#3291ff]/30 transition duration-700 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round">
                {{-- Open Tray Container --}}
                <path d="M3 10.5 4.6 5.4A1.5 1.5 0 0 1 6 4.35h12a1.5 1.5 0 0 1 1.4 1.05L21 10.5" />
                <path d="M3 10.5h4.5l1.2 2.1h6.6l1.2-2.1H21v6.9a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4z" />
                {{-- Drop-in Arrow --}}
                <path d="M12 1.8v4.2" stroke-width="1.2" />
                <path d="M10.4 4.4 12 6l1.6-1.6" stroke-width="1.2" />
            </svg>

            <!-- Floating X-Style QR Code Card in Bottom Right -->
            <div class="absolute bottom-4 right-4 p-3.5 rounded-2xl bg-[#09090b]/90 border border-[#27272a] backdrop-blur-xl flex items-center gap-3 shadow-2xl">
                <div class="w-12 h-12 rounded-xl bg-white p-1 shrink-0 flex items-center justify-center">
                    <svg class="w-full h-full text-black" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14 0h4v4h-4v-4zm-4 4h4v4h-4v-4zm4-4h4v-4h-4v4z"/>
                    </svg>
                </div>
                <div class="min-w-0 pr-1">
                    <p class="text-xs font-bold text-white leading-tight">Pindai untuk membuka di HP</p>
                    <p class="text-[10px] text-[#71717a] mt-0.5 leading-tight">Akses NampungYuk kapan saja.</p>
                </div>
            </div>

        </div>

    </main>

    <!-- FOOTER LINKS ALA X -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 border-t border-[#18181b] text-[11px] text-[#71717a] flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
        <a href="{{ url('/') }}" class="hover:underline">Tentang</a>
        <a href="{{ route('communities.index') }}" class="hover:underline">Komunitas</a>
        <a href="{{ route('projects.index') }}" class="hover:underline">Showcase</a>
        <a href="#" class="hover:underline">Persyaratan</a>
        <a href="#" class="hover:underline">Privasi</a>
        <a href="#" class="hover:underline">Pengembang</a>
        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" class="hover:underline">GitHub</a>
        <span>&copy; {{ date('Y') }} NampungYuk Corp.</span>
    </footer>

</body>
</html>
