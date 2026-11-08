<div x-data="{
        open: {{ ($errors->has('login') || $errors->has('name') || $errors->has('username') || $errors->has('email') || $errors->has('password')) ? 'true' : 'false' }},
        tab: '{{ ($errors->has('name') || $errors->has('username') || ($errors->has('password') && (old('name') || old('username')))) ? 'register' : 'login' }}',
        showPassword: false,
        showRegPassword: false,
        init() {
            window.addEventListener('open-auth-modal', (e) => {
                this.tab = e.detail?.tab || 'login';
                this.open = true;
            });
            window.openAuthModal = (tab = 'login') => {
                this.tab = tab;
                this.open = true;
            };
        }
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop with blur -->
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm"></div>

    <!-- Modal Dialog Center Container -->
    <div class="min-h-full flex items-center justify-center p-3 sm:p-6 selection:bg-[#0B0B0D] selection:text-[#EAE8E1] dark:selection:bg-[#EAE8E1] dark:selection:text-[#0B0B0D]">
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             @click.outside="open = false"
             class="relative w-full max-w-[960px] min-h-[620px] bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl overflow-hidden shadow-2xl grid grid-cols-1 md:grid-cols-[0.95fr_1.05fr] z-10">

            <!-- LEFT COLUMN: Developer Showcase Panel (Warm Linen light / Matte Obsidian dark) -->
            <div class="hidden md:flex bg-[#F6F5F0] dark:bg-[#0B0B0D] text-[#0B0B0D] dark:text-[#EAE8E1] relative overflow-hidden flex-col justify-between p-8 lg:p-10 select-none border-r border-[#E2DFD7] dark:border-[#242426]">
                <!-- Subtle Neutral Mesh -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(11,11,13,0.03),transparent_70%)] dark:bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(234,232,225,0.03),transparent_70%)] pointer-events-none"></div>
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

                <!-- Top Brand Header -->
                <div class="inline-flex items-center gap-2.5 font-bold text-[16px] tracking-tight relative z-10">
                    <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-6 h-6 object-contain block dark:hidden">
                    <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-6 h-6 object-contain hidden dark:block">
                    <span class="text-[#0B0B0D] dark:text-[#EAE8E1] font-extrabold">Nampung<span class="text-[#6E6D67] dark:text-[#8E8D86]">Yuk</span></span>
                    <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold">Dev Showcase</span>
                </div>

                <!-- Middle Editorial Content -->
                <div class="mt-6 mb-auto relative z-10">
                    <h2 class="text-3xl lg:text-[34px] font-extrabold tracking-[-0.03em] leading-[1.12] text-[#0B0B0D] dark:text-[#EAE8E1]">
                        Panggung utama<br>karya codinganmu.
                    </h2>
                    <p class="text-[14px] leading-relaxed text-[#6E6D67] dark:text-[#8E8D86] max-w-[340px] mt-2.5 font-normal">
                        Pamerkan repositori, live demo, dan bangun reputasi di komunitas developer Indonesia.
                    </p>

                    <!-- Sleek Modern IDE / Code Window Card -->
                    <div class="relative mt-6 rounded-xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] shadow-sm overflow-hidden font-mono text-[12px]">
                        <!-- Window Titlebar -->
                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-[#EFECE6] dark:bg-[#161619] border-b border-[#E2DFD7] dark:border-[#242426]">
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
                        <!-- Code Lines -->
                        <div class="p-3.5 leading-relaxed text-[#222225] dark:text-[#DCD9D0] space-y-1">
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
                    <div class="grid grid-cols-3 gap-3 pt-5 border-t border-[#E2DFD7] dark:border-[#242426] mt-6">
                        <div>
                            <div class="text-xl lg:text-2xl font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">1.2K+</div>
                            <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-1 font-semibold">KARYA</div>
                        </div>
                        <div>
                            <div class="text-xl lg:text-2xl font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">40+</div>
                            <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-1 font-semibold">KOMUNITAS</div>
                        </div>
                        <div>
                            <div class="text-xl lg:text-2xl font-extrabold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-none">100%</div>
                            <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mt-1 font-semibold">GRATIS</div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Meta Note -->
                <div class="text-[11px] font-mono uppercase tracking-[0.14em] text-[#6E6D67] dark:text-[#8E8D86] relative z-10 pt-4">
                    KARYA &middot; DISKUSI &middot; KOLABORASI
                </div>
            </div>

            <!-- RIGHT COLUMN: Deep Matte Obsidian / Clean White Form Surface -->
            <div class="bg-white dark:bg-[#121214] text-[#0B0B0D] dark:text-[#EAE8E1] flex flex-col justify-center px-6 py-8 sm:px-10 lg:px-12 relative overflow-y-auto max-h-[92vh]">

                <!-- Close Button -->
                <button @click="open = false" 
                        type="button" 
                        class="absolute top-4 right-4 z-30 w-8 h-8 rounded-lg bg-[#EFECE6] dark:bg-[#161619] hover:bg-[#E2DFD7] dark:hover:bg-[#19191D] text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] flex items-center justify-center transition border border-[#E2DFD7] dark:border-[#242426] cursor-pointer"
                        aria-label="Tutup modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Mobile Brand Header (screens < md) -->
                <div class="md:hidden mb-6 flex items-center justify-between">
                    <div class="inline-flex items-center gap-2 font-bold text-base text-[#0B0B0D] dark:text-[#EAE8E1] tracking-tight">
                        <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain block dark:hidden">
                        <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain hidden dark:block">
                        <span>Nampung<span class="text-[#6E6D67] dark:text-[#8E8D86]">Yuk</span></span>
                    </div>
                </div>

                <!-- Inner Form Container -->
                <div class="w-full max-w-[420px] mx-auto">
                    
                    <!-- Form Title & Subtitle -->
                    <div>
                        <h2 class="text-[26px] sm:text-[28px] font-bold tracking-tight text-[#0B0B0D] dark:text-[#EAE8E1] leading-tight"
                            x-text="tab === 'login' ? 'Selamat datang kembali' : 'Buat akun baru'">
                            Selamat datang kembali
                        </h2>
                        <p class="text-[14px] text-[#6E6D67] dark:text-[#8E8D86] mt-1.5"
                           x-text="tab === 'login' ? 'Masuk untuk melanjutkan eksplorasi dan karyamu.' : 'Mulai pamerkan karya dan terhubung dengan developer lain.'">
                            Masuk untuk melanjutkan eksplorasi dan karyamu.
                        </p>
                    </div>

                    <!-- 2-Tab Segmented Switcher (Token Harbor Capsule) -->
                    <div class="grid grid-cols-2 rounded-xl border border-[#E2DFD7] dark:border-[#242426] bg-[#EFECE6] dark:bg-[#161619] p-1 mt-6 mb-5 text-[13.5px]">
                        <button type="button" 
                                @click="tab = 'login'" 
                                :class="tab === 'login' ? 'bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] font-bold shadow-xs' : 'text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] font-medium'" 
                                class="h-9 rounded-lg flex items-center justify-center transition cursor-pointer">
                            Masuk
                        </button>
                        <button type="button" 
                                @click="tab = 'register'" 
                                :class="tab === 'register' ? 'bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] font-bold shadow-xs' : 'text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] font-medium'" 
                                class="h-9 rounded-lg flex items-center justify-center transition cursor-pointer">
                            Daftar
                        </button>
                    </div>

                    <!-- Social OAuth: Google Button -->
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

                    <!-- TAB 1: LOGIN FORM -->
                    <div x-show="tab === 'login'" x-transition:enter="transition duration-150 ease-out">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="modal-login-id" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">EMAIL ATAU USERNAME</label>
                                <input type="text" 
                                       id="modal-login-id"
                                       name="login" 
                                       value="{{ old('login') }}"
                                       autocomplete="username"
                                       required 
                                       placeholder="nama@email.com atau username" 
                                       class="w-full h-11 px-3.5 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('login') border-rose-500/80 focus:border-rose-500 @enderror">
                                @error('login')
                                    <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="modal-login-pwd" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">PASSWORD</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                           id="modal-login-pwd"
                                           name="password" 
                                           autocomplete="current-password"
                                           required 
                                           placeholder="••••••••" 
                                           class="w-full h-11 px-3.5 pr-11 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('password') border-rose-500/80 focus:border-rose-500 @enderror">
                                    <button type="button" @click="showPassword = !showPassword" aria-label="Tampilkan atau sembunyikan password" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition cursor-pointer">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2 cursor-pointer select-none text-[13px] text-[#0B0B0D] dark:text-[#EAE8E1]">
                                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded border-[#E2DFD7] dark:border-[#242426] bg-white dark:bg-[#161619] accent-[#0B0B0D] dark:accent-[#EAE8E1]">
                                    <span>Ingat saya</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="text-[13px] text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] underline underline-offset-4 transition">Lupa password?</a>
                            </div>

                            <button type="submit" 
                                    class="w-full h-11 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] dark:hover:bg-[#DCD9D0] font-bold text-[14.5px] tracking-wide transition shadow-xs active:scale-[0.99] cursor-pointer mt-2">
                                Masuk
                            </button>
                        </form>

                        <p class="text-[12px] text-[#6E6D67] dark:text-[#8E8D86] mt-4 leading-normal">
                            Dengan melanjutkan, kamu menyetujui <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Ketentuan</span> &amp; <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Privasi</span> NampungYuk.
                        </p>

                        <p class="text-[13.5px] text-[#6E6D67] dark:text-[#8E8D86] mt-4">
                            Belum punya akun? 
                            <button type="button" @click="tab = 'register'" class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline ml-1 cursor-pointer">
                                Buat akun sekarang
                            </button>
                        </p>
                    </div>

                    <!-- TAB 2: REGISTER FORM -->
                    <div x-show="tab === 'register'" x-cloak x-transition:enter="transition duration-150 ease-out">
                        <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="modal-reg-name" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">NAMA LENGKAP</label>
                                    <input type="text" 
                                           id="modal-reg-name"
                                           name="name" 
                                           value="{{ old('name') }}"
                                           autocomplete="name"
                                           required 
                                           placeholder="Budi Santoso" 
                                           class="w-full h-11 px-3.5 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('name') border-rose-500/80 focus:border-rose-500 @enderror">
                                    @error('name') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="modal-reg-username" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">USERNAME</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px] font-mono text-[#6E6D67] dark:text-[#8E8D86]">@</span>
                                        <input type="text" 
                                               id="modal-reg-username"
                                               name="username" 
                                               value="{{ old('username') }}"
                                               autocomplete="username"
                                               required 
                                               placeholder="budisantoso" 
                                               class="w-full h-11 pl-8 pr-3 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] font-mono placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('username') border-rose-500/80 focus:border-rose-500 @enderror">
                                    </div>
                                    @error('username') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="modal-reg-email" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">EMAIL</label>
                                <input type="email" 
                                       id="modal-reg-email"
                                       name="email" 
                                       value="{{ old('email') }}"
                                       autocomplete="email"
                                       required 
                                       placeholder="nama@email.com" 
                                       class="w-full h-11 px-3.5 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('email') border-rose-500/80 focus:border-rose-500 @enderror">
                                @error('email') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="modal-reg-pwd" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">PASSWORD</label>
                                    <div class="relative">
                                        <input :type="showRegPassword ? 'text' : 'password'" 
                                               id="modal-reg-pwd"
                                               name="password" 
                                               autocomplete="new-password" 
                                               required 
                                               placeholder="Min. 8 karakter" 
                                               class="w-full h-11 px-3.5 pr-11 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition @error('password') border-rose-500/80 focus:border-rose-500 @enderror">
                                        <button type="button" @click="showRegPassword = !showRegPassword" aria-label="Tampilkan atau sembunyikan password" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition cursor-pointer">
                                            <svg x-show="!showRegPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showRegPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                    @error('password') <p class="text-rose-500 text-[11px] font-medium mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="modal-reg-pwd-conf" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">KONFIRMASI</label>
                                    <input :type="showRegPassword ? 'text' : 'password'" 
                                           id="modal-reg-pwd-conf"
                                           name="password_confirmation" 
                                           autocomplete="new-password" 
                                           required 
                                           placeholder="Ulangi sandi" 
                                           class="w-full h-11 px-3.5 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] text-[14px] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#6E6D67] dark:placeholder:text-[#8E8D86] outline-none transition">
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full h-11 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] dark:hover:bg-[#DCD9D0] font-bold text-[14.5px] tracking-wide transition shadow-xs active:scale-[0.99] cursor-pointer mt-2">
                                Buat Akun
                            </button>
                        </form>

                        <p class="text-[12px] text-[#6E6D67] dark:text-[#8E8D86] mt-4 leading-normal">
                            Dengan melanjutkan, kamu menyetujui <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Ketentuan</span> &amp; <span class="underline underline-offset-4 text-[#0B0B0D] dark:text-[#EAE8E1]">Privasi</span> NampungYuk.
                        </p>

                        <p class="text-[13.5px] text-[#6E6D67] dark:text-[#8E8D86] mt-4">
                            Sudah punya akun? 
                            <button type="button" @click="tab = 'login'" class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline ml-1 cursor-pointer">
                                Masuk ke akun
                            </button>
                        </p>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
