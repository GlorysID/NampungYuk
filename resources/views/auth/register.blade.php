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

    <!-- Fonts: Geist + Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F5F0] dark:bg-[#0B0B0D] text-[#0B0B0D] dark:text-[#EAE8E1] min-h-screen antialiased selection:bg-[#0B0B0D] selection:text-[#EAE8E1] dark:selection:bg-[#EAE8E1] dark:selection:text-[#0B0B0D]">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2" x-data="{ showPassword: false }">

        <!-- LEFT HALF: Developer Showcase Panel (Full-bleed 50vw) -->
        <div class="hidden lg:flex bg-[#F6F5F0] dark:bg-[#0B0B0D] text-[#0B0B0D] dark:text-[#EAE8E1] relative overflow-hidden flex-col justify-between p-12 lg:p-16 min-h-screen select-none border-r border-[#E2DFD7] dark:border-[#242426]">
            <!-- Subtle Neutral Mesh -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(11,11,13,0.03),transparent_70%)] dark:bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(234,232,225,0.03),transparent_70%)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

            <!-- Top-Left Brand Header -->
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 font-bold text-[17px] tracking-tight relative z-10 hover:opacity-90 transition">
                <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-7 h-7 object-contain block dark:hidden">
                <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-7 h-7 object-contain hidden dark:block">
                <span class="text-[#0B0B0D] dark:text-[#EAE8E1] font-extrabold">Nampung<span class="text-[#6E6D67] dark:text-[#8E8D86]">Yuk</span></span>
                <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold">Dev Showcase</span>
            </a>

            <!-- Middle Display Headline, Terminal Code Card & Metrics -->
            <div class="my-auto py-10 relative z-10 max-w-[480px]">
                <h2 class="text-4xl lg:text-[44px] font-extrabold tracking-[-0.03em] leading-[1.08] text-[#0B0B0D] dark:text-[#EAE8E1]">
                    Panggung utama<br>karya codinganmu.
                </h2>
                <p class="text-[15.5px] leading-relaxed text-[#6E6D67] dark:text-[#8E8D86] mt-4 font-normal">
                    Pamerkan repositori, live demo, dan bangun reputasi bersama ribuan developer Indonesia.
                </p>

                <!-- Sleek Modern IDE / Terminal Window Card -->
                <div class="relative mt-7 rounded-xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] shadow-sm overflow-hidden font-mono text-[12.5px]">
                    <!-- Window Titlebar -->
                    <div class="flex items-center justify-between px-4 py-2.5 bg-[#EFECE6] dark:bg-[#161619] border-b border-[#E2DFD7] dark:border-[#242426]">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <div class="text-[11px] text-[#6E6D67] dark:text-[#8E8D86] font-medium tracking-tight flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#6E6D67] dark:text-[#8E8D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            <span>nampungyuk.config.ts</span>
                        </div>
                        <span class="text-[10px] text-[#6E6D67] dark:text-[#8E8D86] bg-[#EFECE6] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] px-1.5 py-0.5 rounded font-mono font-medium">TS</span>
                    </div>

                    <!-- Code Content -->
                    <div class="p-4 leading-relaxed text-[#222225] dark:text-[#DCD9D0] space-y-1">
                        <div><span class="text-violet-600 dark:text-violet-400 font-medium">export default</span> <span class="text-blue-600 dark:text-blue-400 font-medium">defineProject</span>({</div>
                        <div class="pl-4"><span class="text-[#6E6D67] dark:text-[#8E8D86]">name:</span> <span class="text-emerald-600 dark:text-emerald-400">"KaryaKeren.id"</span>,</div>
                        <div class="pl-4"><span class="text-[#6E6D67] dark:text-[#8E8D86]">tech:</span> [<span class="text-emerald-600 dark:text-emerald-400">"Laravel"</span>, <span class="text-emerald-600 dark:text-emerald-400">"Vue"</span>, <span class="text-emerald-600 dark:text-emerald-400">"Tailwind"</span>],</div>
                        <div class="pl-4 flex items-center gap-2 flex-wrap">
                            <span class="text-[#6E6D67] dark:text-[#8E8D86]">status:</span> <span class="text-emerald-600 dark:text-emerald-400">"live-demo"</span>,
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 font-sans font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Demo
                            </span>
                        </div>
                        <div class="pl-4 flex items-center gap-2">
                            <span class="text-[#6E6D67] dark:text-[#8E8D86]">upvotes:</span> <span class="text-amber-600 dark:text-amber-400">691</span>
                            <span class="text-[10px] text-[#6E6D67] dark:text-[#8E8D86] font-sans">#1 Hari Ini</span>
                        </div>
                        <div>});</div>
                    </div>
                </div>

                <!-- 3 Sharp Metric Columns -->
                <div class="grid grid-cols-3 gap-6 pt-7 border-t border-[#E2DFD7] dark:border-[#242426] mt-8">
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">1.2K+</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-2 font-semibold">KARYA TERPAMER</div>
                    </div>
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">40+</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-2 font-semibold">KOMUNITAS</div>
                    </div>
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">100%</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-2 font-semibold">GRATIS &amp; TERBUKA</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Footer Metatag -->
            <div class="text-[11px] font-mono uppercase tracking-[0.16em] text-[#6E6D67] dark:text-[#8E8D86] relative z-10">
                KARYA &middot; DISKUSI &middot; KOLABORASI INDONESIA
            </div>
        </div>

        <!-- RIGHT HALF: Form Surface (Full-bleed 50vw) -->
        <div class="w-full min-h-screen bg-white dark:bg-[#121214] text-[#0B0B0D] dark:text-[#EAE8E1] flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-16 relative overflow-y-auto">

            <!-- Top Navigation Bar -->
            <div class="absolute top-6 left-6 right-6 sm:top-8 sm:left-12 sm:right-12 flex items-center justify-between z-20">
                <a href="{{ url('/') }}" class="lg:hidden inline-flex items-center gap-2 font-bold text-base text-[#0B0B0D] dark:text-[#EAE8E1] tracking-tight">
                    <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain block dark:hidden">
                    <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain hidden dark:block">
                    <span>Nampung<span class="text-[#6E6D67] dark:text-[#8E8D86]">Yuk</span></span>
                </a>
                <div class="hidden lg:block"></div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition">
                    <span>&larr;</span>
                    <span>Beranda</span>
                </a>
            </div>

            <!-- Inner Form Wrapper -->
            <div class="w-full max-w-[420px] mx-auto my-auto py-12">

                <!-- Header -->
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-bold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-tight">
                        Buat akun baru
                    </h1>
                    <p class="text-[15px] text-[#6E6D67] dark:text-[#8E8D86] mt-1.5">
                        Mulai pamerkan karya dan terhubung dengan developer lain.
                    </p>
                </div>

                <!-- 2-Tab Segmented Switcher (Token Harbor Capsule) -->
                <div class="grid grid-cols-2 rounded-xl border border-[#E2DFD7] dark:border-[#242426] bg-[#EFECE6] dark:bg-[#161619] p-1 mt-7 mb-5 text-[14px]">
                    <a href="{{ route('login') }}" class="text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] font-medium h-10 rounded-lg flex items-center justify-center transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] font-bold shadow-xs h-10 rounded-lg flex items-center justify-center transition">
                        Daftar
                    </a>
                </div>

                <!-- Social OAuth Button: Google -->
                <div>
                    <a href="{{ route('auth.google') }}" 
                       class="h-11 w-full border border-[#E2DFD7] dark:border-[#242426] bg-white dark:bg-[#161619] hover:bg-[#EFECE6] dark:hover:bg-[#19191D] hover:border-[#D0CDC3] dark:hover:border-[#333336] text-[14px] font-semibold text-[#0B0B0D] dark:text-[#EAE8E1] flex items-center justify-center gap-2.5 rounded-xl transition cursor-pointer shadow-xs">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Lanjutkan dengan Google</span>
                    </a>

                    <!-- Centered ATAU Divider -->
                    <div class="relative my-4 text-center">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#E2DFD7] dark:border-[#242426]"></div></div>
                        <span class="relative bg-white dark:bg-[#121214] px-3 text-[11px] font-mono font-bold uppercase tracking-[0.14em] text-[#6E6D67] dark:text-[#8E8D86]">ATAU</span>
                    </div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Nama Lengkap & Username (2-col grid) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="name" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">NAMA LENGKAP</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   autocomplete="name" 
                                   required 
                                   autofocus 
                                   placeholder="Budi Santoso" 
                                   class="w-full h-12 px-4 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('name') border-rose-500/80 focus:border-rose-500 @enderror">
                            @error('name') <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="username" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">USERNAME</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[14px] font-mono text-[#6E6D67] dark:text-[#8E8D86]">@</span>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}" 
                                       autocomplete="username" 
                                       required 
                                       placeholder="budisantoso" 
                                       class="w-full h-12 pl-8 pr-4 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] font-mono placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('username') border-rose-500/80 focus:border-rose-500 @enderror">
                            </div>
                            @error('username') <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">EMAIL</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               autocomplete="email" 
                               required 
                               placeholder="nama@email.com" 
                               class="w-full h-12 px-4 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('email') border-rose-500/80 focus:border-rose-500 @enderror">
                        @error('email') <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password & Konfirmasi Password (2-col grid) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="password" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">PASSWORD</label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       name="password" 
                                       autocomplete="new-password" 
                                       required 
                                       placeholder="Min. 8 karakter" 
                                       class="w-full h-12 px-4 pr-11 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('password') border-rose-500/80 focus:border-rose-500 @enderror">
                                <button type="button" @click="showPassword = !showPassword" aria-label="Tampilkan atau sembunyikan password" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition cursor-pointer">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password') <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">KONFIRMASI</label>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   autocomplete="new-password" 
                                   required 
                                   placeholder="Ulangi sandi" 
                                   class="w-full h-12 px-4 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full h-12 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] dark:hover:bg-[#DCD9D0] font-bold text-[15px] transition shadow-xs active:scale-[0.99] cursor-pointer mt-2">
                        Buat Akun
                    </button>
                </form>

                <!-- Terms footnote -->
                <p class="text-[12px] text-[#6E6D67] dark:text-[#8E8D86] mt-4 leading-normal">
                    Dengan melanjutkan, kamu menyetujui <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Ketentuan</span> &amp; <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Privasi</span> NampungYuk.
                </p>

                <!-- Switcher Footer -->
                <p class="text-[14px] text-[#6E6D67] dark:text-[#8E8D86] mt-4">
                    Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline ml-1">Masuk ke akun</a>
                </p>

            </div>

        </div>

    </div>

</body>
</html>
