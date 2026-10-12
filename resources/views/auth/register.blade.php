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
<body class="bg-[#f4f4f5] dark:bg-[#060709] text-[#18181b] dark:text-[#fafafa] min-h-screen flex items-center justify-center p-3 sm:p-6 relative antialiased selection:bg-[#3291ff]/25 selection:text-[#0070f3]"
      x-data="{ showPassword: false }">

    <!-- Ambient Glow Backdrop -->
    <div class="fixed inset-0 bg-black/60 dark:bg-black/85 backdrop-blur-md pointer-events-none"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-[#0070f3]/10 dark:bg-[#0070f3]/15 blur-[120px] pointer-events-none"></div>

    <!-- Centered Modal Box (Persegi panjang kesamping: 2 Kolom) -->
    <div class="relative w-full max-w-md md:max-w-3xl lg:max-w-4xl bg-white dark:bg-[#000000] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] flex flex-col md:flex-row text-[#18181b] dark:text-[#fafafa] z-10">

        <!-- Close Button (Kembali ke Beranda) -->
        <a href="{{ url('/') }}" 
           class="absolute top-4 right-4 z-30 w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 text-[#71717a] hover:text-[#18181b] dark:text-[#a1a1aa] dark:hover:text-white flex items-center justify-center transition border border-black/5 dark:border-white/10 cursor-pointer"
           title="Kembali ke Beranda"
           aria-label="Tutup modal">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>

        <!-- LEFT COLUMN: Register Form (50% on md+) -->
        <div class="w-full md:w-1/2 p-6 sm:p-8 flex flex-col justify-between space-y-4">
            
            <div>
                <!-- Brand header badge -->
                <div class="mb-3.5 flex items-center justify-between">
                    <x-logo size="sm" :href="url('/')" />
                    <a href="{{ route('login') }}" class="mr-10 md:mr-0 text-xs font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                        Masuk &rarr;
                    </a>
                </div>

                <!-- Headline -->
                <div class="space-y-1 mb-4">
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#18181b] dark:text-white">
                        Bergabunglah hari ini.
                    </h1>
                    <p class="text-xs text-[#71717a] dark:text-[#a1a1aa] leading-relaxed line-clamp-2">
                        Buat akun untuk memamerkan karya codingmu dan terhubung dengan sesama developer.
                    </p>
                </div>

                <!-- Social OAuth Button: Google -->
                <div>
                    <a href="{{ route('auth.google') }}" 
                       class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-[#f4f4f5] hover:bg-[#e4e4e7] dark:bg-[#18181b] dark:hover:bg-[#27272a] text-[#18181b] dark:text-white rounded-xl font-bold text-xs transition duration-150 border border-[#e4e4e7] dark:border-[#27272a] shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Daftar dengan Google</span>
                    </a>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center my-3.5">
                        <div class="border-t border-[#e4e4e7] dark:border-[#27272a] w-full"></div>
                        <span class="bg-white dark:bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider">
                            atau daftar manual
                        </span>
                    </div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="name" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Nama Lengkap</label>
                            <input type="text" 
                                   id="name"
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required 
                                   autofocus
                                   placeholder="Budi Santoso" 
                                   class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                            @error('name') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="username" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Username</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-mono text-[#71717a]">@</span>
                                <input type="text" 
                                       id="username"
                                       name="username" 
                                       value="{{ old('username') }}"
                                       required 
                                       placeholder="budisantoso" 
                                       class="w-full pl-6 pr-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs font-mono placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                            </div>
                            @error('username') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Email Aktif</label>
                        <input type="email" 
                               id="email"
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               placeholder="nama@email.com" 
                               class="w-full px-3.5 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                        @error('email') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="password" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Password</label>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password"
                                   name="password" 
                                   required 
                                   placeholder="Min. 8 karakter" 
                                   class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                            @error('password') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Konfirmasi</label>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password_confirmation"
                                   name="password_confirmation" 
                                   required 
                                   placeholder="Ulangi sandi" 
                                   class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-1.5 text-[11px] text-[#71717a] cursor-pointer select-none">
                            <input type="checkbox" @change="showPassword = $event.target.checked" class="rounded border-[#e4e4e7] dark:border-[#27272a] text-[#0070f3]">
                            <span>Lihat password</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full mt-2 py-2.5 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm cursor-pointer">
                        Buat Akun & Bergabung
                    </button>
                </form>
            </div>

            <!-- Switcher Footer & Terms -->
            <div class="space-y-2">
                <p class="text-[10px] text-[#71717a] dark:text-[#52525b] leading-tight">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan dan Kebijakan Privasi NampungYuk.
                </p>
                <div class="pt-2.5 border-t border-[#e4e4e7] dark:border-[#18181b] flex items-center justify-between text-xs">
                    <span class="text-[#71717a]">Sudah punya akun?</span>
                    <a href="{{ route('login') }}" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline cursor-pointer">
                        Masuk ke Akun &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Elevated Brand Identity Stage (Side-by-side kesamping pada md+) -->
        <div class="hidden md:flex md:w-1/2 relative bg-gradient-to-br from-[#f8f9fa] via-[#f1f3f5] to-[#e9ecef] dark:from-[#0c0d14] dark:via-[#07080c] dark:to-[#030305] border-t md:border-t-0 md:border-l border-[#e4e4e7] dark:border-[#1f1f23] p-7 lg:p-8 flex-col justify-center items-center text-center overflow-hidden select-none">
            
            <!-- Background Subtle Tech Grid & Radial Glows -->
            <div class="absolute inset-0 bg-[radial-gradient(#0070f3_1px,transparent_1px)] [background-size:20px_20px] opacity-[0.07] dark:opacity-10 pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-[#0070f3]/10 dark:bg-[#0070f3]/20 blur-[110px] pointer-events-none"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-[#00c2cb]/10 dark:bg-[#00c2cb]/15 blur-[80px] pointer-events-none"></div>

            <div class="relative z-10 w-full max-w-sm mx-auto space-y-6 flex flex-col items-center">
                
                <!-- Top: Brand Eyebrow & Value Statement -->
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[11px] text-[#63636b] dark:text-[#a1a1aa] font-medium backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c2cb] animate-pulse"></span>
                        <span>Showroom Developer Indonesia</span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-[#18181b] dark:text-white tracking-tight leading-snug">
                        Tempat karya codinganmu <br><span class="ny-gradient-text">mendapatkan panggung.</span>
                    </h3>
                </div>

                <!-- Center: Floating Brand Pedestal & Hero Emblem -->
                <div class="flex flex-col items-center text-center space-y-3.5">
                    <!-- Frosted Glass Pedestal with Ambient Neon Core -->
                    <div class="relative w-36 h-36 rounded-3xl bg-white/80 dark:bg-gradient-to-b dark:from-white/[0.08] dark:to-white/[0.02] border border-[#e4e4e7] dark:border-white/10 shadow-[0_20px_50px_rgba(0,112,243,0.12)] dark:shadow-[0_20px_50px_rgba(0,112,243,0.3)] backdrop-blur-xl flex items-center justify-center p-5 group hover:border-[#00c2cb]/40 transition-all duration-500">
                        <div class="absolute inset-2 rounded-2xl bg-radial from-[#0070f3]/15 dark:from-[#0070f3]/25 via-[#00c2cb]/10 to-transparent pointer-events-none"></div>

                        <!-- Hero Brand Logo Icon -->
                        <img src="{{ asset('images/lightmode-icon.png') }}" 
                             alt="NampungYuk Logo" 
                             class="w-full h-full object-contain block dark:hidden drop-shadow-md transform group-hover:scale-105 transition-transform duration-500">
                        <img src="{{ asset('images/darkmode-icon.png') }}" 
                             alt="NampungYuk Logo" 
                             class="w-full h-full object-contain hidden dark:block filter drop-shadow-[0_12px_24px_rgba(0,0,0,0.9)] transform group-hover:scale-105 transition-transform duration-500">

                        <!-- Floating Micro Pill 1: Code -->
                        <div class="absolute -top-2.5 -right-2.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#00c2cb]/40 text-[#008f97] dark:text-[#00c2cb] text-[10px] font-mono font-bold shadow-md flex items-center gap-1">
                            <span>&lt;/&gt;</span>
                            <span>Code</span>
                        </div>

                        <!-- Floating Micro Pill 2: Community -->
                        <div class="absolute -bottom-2.5 -left-2.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-[#090a0f] border border-[#3291ff]/40 text-[#0070f3] dark:text-[#3291ff] text-[10px] font-mono font-bold shadow-md flex items-center gap-1">
                            <span>💬</span>
                            <span>Komunitas</span>
                        </div>
                    </div>

                    <!-- Brand Name & Description -->
                    <div class="space-y-1 max-w-[260px]">
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

    </div>

</body>
</html>
