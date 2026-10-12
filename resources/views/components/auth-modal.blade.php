<div x-data="{
        open: false,
        tab: 'login', // 'login' or 'register'
        regStep: {{ $errors->has('password') && (old('name') || old('username')) ? 2 : 1 }},
        showPassword: false,
        showRegPassword: false,
        showRegPasswordConfirm: false,

        // Step 1 Form Models
        regName: '{{ old('name', '') }}',
        regUsername: '{{ old('username', '') }}',
        regEmail: '{{ old('email', '') }}',
        regPassword: '',
        regPasswordConfirmation: '',
        regError: '',
        regPasswordError: '',

        init() {
            window.addEventListener('open-auth-modal', (e) => {
                this.setTab(e.detail?.tab || 'login');
                this.open = true;
            });
            window.openAuthModal = (tab = 'login') => {
                this.setTab(tab);
                this.open = true;
            };
        },

        setTab(tab = 'login') {
            this.tab = tab;
            this.regStep = 1;
            this.showPassword = false;
            this.showRegPassword = false;
            this.showRegPasswordConfirm = false;
            this.regError = '';
            this.regPasswordError = '';
        },

        getTitle() {
            if (this.tab === 'login') return 'Sedang tren saat ini.';
            return this.regStep === 1 ? 'Bergabunglah hari ini.' : 'Buat password akun.';
        },

        getSubtitle() {
            if (this.tab === 'login') return 'Masuk untuk memamerkan karya, bookmark project, dan berdiskusi dengan sesama developer.';
            return this.regStep === 1 ? 'Langkah 1 dari 2: Isi identitas profil dan alamat email akunmu.' : 'Langkah 2 dari 2: Buat kata sandi aman untuk masuk ke akunmu.';
        },

        nextRegStep() {
            this.regError = '';
            if (!this.regName.trim()) {
                this.regError = 'Nama lengkap wajib diisi.';
                return;
            }
            if (!this.regUsername.trim()) {
                this.regError = 'Username wajib diisi.';
                return;
            }
            if (this.regUsername.length < 3) {
                this.regError = 'Username minimal 3 karakter.';
                return;
            }
            if (!/^[a-zA-Z0-9_]+$/.test(this.regUsername)) {
                this.regError = 'Username hanya huruf, angka, & underscore.';
                return;
            }
            if (!this.regEmail.trim()) {
                this.regError = 'Email wajib diisi.';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.regEmail)) {
                this.regError = 'Format email tidak valid.';
                return;
            }
            this.regStep = 2;
        },

        submitRegister(e) {
            this.regPasswordError = '';
            if (!this.regPassword || this.regPassword.length < 8) {
                e.preventDefault();
                this.regPasswordError = 'Password minimal 8 karakter.';
                return;
            }
            if (this.regPassword !== this.regPasswordConfirmation) {
                e.preventDefault();
                this.regPasswordError = 'Konfirmasi password tidak cocok.';
                return;
            }
        }
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"></div>

    <!-- Modal Dialog Center Container -->
    <div class="min-h-full flex items-center justify-center p-3 sm:p-5">
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="relative w-full max-w-md lg:max-w-3xl bg-white dark:bg-[#000000] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.25)] dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] grid grid-cols-1 lg:grid-cols-2 text-[#18181b] dark:text-[#fafafa]">

            <!-- Close Button (X-style rounded button) -->
            <button @click="open = false" 
                    type="button" 
                    class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[#63636b] hover:text-[#18181b] dark:text-[#a1a1aa] dark:hover:text-white flex items-center justify-center transition border border-black/10 dark:border-white/10"
                    aria-label="Tutup modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- LEFT COLUMN: Forms & Auth Actions -->
            <div class="p-6 sm:p-8 flex flex-col justify-between space-y-4 min-h-[510px]">
                
                <div>
                    <!-- Brand header badge -->
                    <div class="mb-3.5">
                        <x-logo size="sm" :href="null" />
                    </div>

                    <!-- Headlines (Inspired by X) -->
                    <div class="space-y-1 mb-4">
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-[#18181b] dark:text-white" 
                            x-text="getTitle()"></h2>
                        <p class="text-xs text-[#63636b] dark:text-[#a1a1aa] leading-relaxed line-clamp-2"
                           x-text="getSubtitle()"></p>
                    </div>

                    <!-- Social Login: Google OAuth Button (Pill style X) - Tampil di Login & Register Langkah 1 -->
                    <div x-show="tab === 'login' || (tab === 'register' && regStep === 1)" class="space-y-3">
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
                            <span class="bg-white dark:bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider"
                                  x-text="tab === 'login' ? 'atau masuk manual' : 'atau daftar manual'"></span>
                        </div>
                    </div>

                    <!-- Register Langkah 2: Bar Status & Tombol Kembali (Pengganti Google Button & Divider) -->
                    <div x-show="tab === 'register' && regStep === 2" x-cloak class="space-y-3">
                        <div class="flex items-center justify-between py-2 px-3.5 rounded-2xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-xs">
                            <button type="button" 
                                    @click="regStep = 1; regError = '';" 
                                    class="inline-flex items-center gap-1.5 font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                <span>Ubah Data</span>
                            </button>
                            <div class="text-[11px] text-[#71717a] flex items-center gap-1.5 max-w-[160px] truncate">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="truncate" x-text="regEmail || regUsername"></span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="relative flex items-center justify-center my-3.5">
                            <div class="border-t border-[#e4e4e7] dark:border-[#27272a] w-full"></div>
                            <span class="bg-white dark:bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider">Langkah 2 / 2</span>
                        </div>
                    </div>

                    <!-- TAB 1: LOGIN FORM -->
                    <div x-show="tab === 'login'" x-transition:enter="transition duration-150 ease-out" class="space-y-3">
                        <form method="POST" action="{{ route('login') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="modal-login-id" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Email atau Username</label>
                                <input type="text" 
                                       id="modal-login-id"
                                       name="login" 
                                       required 
                                       placeholder="dev@nampungyuk.id atau username" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] focus:ring-1 focus:ring-[#0070f3] dark:focus:ring-[#3291ff] outline-none transition">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="modal-login-pwd" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa]">Password</label>
                                </div>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                           id="modal-login-pwd"
                                           name="password" 
                                           required 
                                           placeholder="••••••••" 
                                           class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] focus:ring-1 focus:ring-[#0070f3] dark:focus:ring-[#3291ff] outline-none transition">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-[#18181b] dark:hover:text-white text-xs">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full mt-2 py-2.5 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm">
                                Masuk ke Akun
                            </button>
                        </form>

                        <div class="pt-2.5 border-t border-[#e4e4e7] dark:border-[#18181b] flex items-center justify-between text-xs">
                            <span class="text-[#71717a]">Belum punya akun?</span>
                            <button type="button" @click="setTab('register')" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                                Buat Akun Baru &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: REGISTER FORM (BERTAHAP 2 LANGKAH) -->
                    <div x-show="tab === 'register'" x-cloak x-transition:enter="transition duration-150 ease-out" class="space-y-3">
                        <form method="POST" 
                              action="{{ route('register') }}" 
                              @submit="submitRegister($event)" 
                              @keydown.enter="if(regStep === 1) { $event.preventDefault(); nextRegStep(); }"
                              class="space-y-3">
                            @csrf

                            <!-- LANGKAH 1: IDENTITAS PROFIL & EMAIL -->
                            <div x-show="regStep === 1" class="space-y-3">
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label for="modal-reg-name" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Nama Lengkap</label>
                                        <input type="text" 
                                               id="modal-reg-name"
                                               name="name" 
                                               x-model="regName"
                                               required 
                                               placeholder="Rizky Pratama" 
                                               class="w-full px-3 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                                    </div>
                                    <div>
                                        <label for="modal-reg-username" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Username</label>
                                        <input type="text" 
                                               id="modal-reg-username"
                                               name="username" 
                                               x-model="regUsername"
                                               required 
                                               placeholder="rizkydev" 
                                               class="w-full px-3 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                                    </div>
                                </div>

                                <div>
                                    <label for="modal-reg-email" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Email</label>
                                    <input type="email" 
                                           id="modal-reg-email"
                                           name="email" 
                                           x-model="regEmail"
                                           required 
                                           placeholder="rizky@example.com" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                                </div>

                                <!-- Error inline Langkah 1 -->
                                <p x-show="regError" x-text="regError" class="text-rose-500 dark:text-rose-400 text-[11px] font-medium leading-tight"></p>

                                <button type="button" 
                                        @click="nextRegStep()" 
                                        class="w-full mt-2 py-2.5 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm flex items-center justify-center gap-1.5">
                                    <span>Lanjut ke Password</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>

                            <!-- LANGKAH 2: PASSWORD & KONFIRMASI -->
                            <div x-show="regStep === 2" x-cloak class="space-y-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="modal-reg-pwd" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa]">Password</label>
                                        <span class="text-[10px] text-[#71717a]">Min. 8 karakter</span>
                                    </div>
                                    <div class="relative">
                                        <input :type="showRegPassword ? 'text' : 'password'" 
                                               id="modal-reg-pwd"
                                               name="password" 
                                               x-model="regPassword"
                                               placeholder="••••••••" 
                                               class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                                        <button type="button" @click="showRegPassword = !showRegPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-[#18181b] dark:hover:text-white text-xs">
                                            <svg x-show="!showRegPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showRegPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="modal-reg-pwd-conf" class="block text-[11px] font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Konfirmasi Password</label>
                                    <div class="relative">
                                        <input :type="showRegPasswordConfirm ? 'text' : 'password'" 
                                               id="modal-reg-pwd-conf"
                                               name="password_confirmation" 
                                               x-model="regPasswordConfirmation"
                                               placeholder="••••••••" 
                                               class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#f8f9fa] dark:bg-[#09090b] border border-[#e4e4e7] dark:border-[#27272a] text-[#18181b] dark:text-white text-xs placeholder-[#a1a1aa] dark:placeholder-[#52525b] focus:border-[#0070f3] dark:focus:border-[#3291ff] outline-none transition">
                                        <button type="button" @click="showRegPasswordConfirm = !showRegPasswordConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-[#18181b] dark:hover:text-white text-xs">
                                            <svg x-show="!showRegPasswordConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showRegPasswordConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Error inline Langkah 2 -->
                                <p x-show="regPasswordError" x-text="regPasswordError" class="text-rose-500 dark:text-rose-400 text-[11px] font-medium leading-tight"></p>

                                <button type="submit" 
                                        class="w-full mt-2 py-2.5 rounded-full bg-[#18181b] hover:bg-[#27272a] text-white dark:bg-white dark:hover:bg-[#e4e4e7] dark:text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm">
                                    Buat Akun & Bergabung
                                </button>
                            </div>
                        </form>

                        <div class="pt-2.5 border-t border-[#e4e4e7] dark:border-[#18181b] flex items-center justify-between text-xs">
                            <span class="text-[#71717a]">Sudah punya akun?</span>
                            <button type="button" @click="setTab('login')" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                                Masuk ke Akun &rarr;
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Terms footnote -->
                <p class="text-[10px] text-[#71717a] dark:text-[#52525b] leading-tight">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan dan Kebijakan Privasi NampungYuk.
                </p>

            </div>

            <!-- RIGHT COLUMN: Elevated Brand Identity Stage -->
            <div class="hidden lg:flex relative bg-gradient-to-br from-[#f8f9fa] via-[#f1f3f5] to-[#e9ecef] dark:from-[#0c0d14] dark:via-[#07080c] dark:to-[#030305] border-l border-[#e4e4e7] dark:border-[#1f1f23] p-6 sm:p-8 flex-col justify-center items-center text-center overflow-hidden select-none">
                
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
                            <!-- Neon radial flare inside pedestal -->
                            <div class="absolute inset-2 rounded-2xl bg-radial from-[#0070f3]/15 dark:from-[#0070f3]/25 via-[#00c2cb]/10 to-transparent pointer-events-none"></div>

                            <!-- Hero Brand Logo Icon (Dual: Light mode and Dark mode) -->
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

    </div>

</div>
