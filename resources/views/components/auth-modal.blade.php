<div x-data="{
        open: false,
        tab: 'login', // 'login' or 'register'
        showPassword: false,
        init() {
            window.addEventListener('open-auth-modal', (e) => {
                this.tab = e.detail?.tab || 'login';
                this.open = true;
                this.showPassword = false;
            });
            window.openAuthModal = (tab = 'login') => {
                this.tab = tab;
                this.open = true;
                this.showPassword = false;
            };
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
         class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

    <!-- Modal Dialog Center Container -->
    <div class="min-h-full flex items-center justify-center p-3 sm:p-5">
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="relative w-full max-w-4xl bg-[#000000] border border-[#27272a] rounded-3xl overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] grid grid-cols-1 lg:grid-cols-12 text-[#fafafa]">

            <!-- Close Button (X-style rounded button) -->
            <button @click="open = false" 
                    type="button" 
                    class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/5 hover:bg-white/10 text-[#a1a1aa] hover:text-white flex items-center justify-center transition border border-white/10"
                    aria-label="Tutup modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- LEFT COLUMN: Forms & Auth Actions (7 cols) -->
            <div class="lg:col-span-7 p-6 sm:p-9 sm:py-10 flex flex-col justify-between space-y-6">
                
                <div>
                    <!-- Brand header badge -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-xl ny-gradient-bg text-white flex items-center justify-center font-bold text-sm shadow-md">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 10.5 4.6 5.4A1.5 1.5 0 0 1 6 4.35h12a1.5 1.5 0 0 1 1.4 1.05L21 10.5" />
                                <path d="M3 10.5h4.5l1.2 2.1h6.6l1.2-2.1H21v6.9a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4z" />
                                <path d="M12 1.8v4.2" />
                                <path d="M10.4 4.4 12 6l1.6-1.6" />
                            </svg>
                        </div>
                        <span class="font-extrabold text-base tracking-tight text-white">Nampung<span class="ny-gradient-text">Yuk</span></span>
                    </div>

                    <!-- Headlines (Inspired by X) -->
                    <div class="space-y-1 mb-6">
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white" 
                            x-text="tab === 'login' ? 'Sedang tren saat ini.' : 'Bergabunglah hari ini.'"></h2>
                        <p class="text-xs text-[#a1a1aa] leading-relaxed"
                           x-text="tab === 'login' ? 'Masuk untuk memamerkan karya, bookmark project, dan berdiskusi dengan sesama developer.' : 'Daftar akun gratis dan pamerkan portofolio codinganmu ke ribuan developer Indonesia.'"></p>
                    </div>

                    <!-- Social Login: Google OAuth Button (Pill style X) -->
                    <div class="space-y-3">
                        <a href="{{ route('auth.google') }}" 
                           class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white hover:bg-[#e4e4e7] text-[#09090b] rounded-full font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm">
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
                            <span class="bg-[#000000] px-3 text-[10px] font-mono uppercase text-[#71717a] tracking-wider">atau</span>
                        </div>
                    </div>

                    <!-- TAB 1: LOGIN FORM -->
                    <div x-show="tab === 'login'" x-transition:enter="transition duration-150 ease-out" class="space-y-3.5">
                        <form method="POST" action="{{ route('login') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="modal-login-id" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Email atau Username</label>
                                <input type="text" 
                                       id="modal-login-id"
                                       name="login" 
                                       required 
                                       placeholder="dev@nampungyuk.id atau username" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] focus:ring-1 focus:ring-[#3291ff] outline-none transition">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="modal-login-pwd" class="block text-[11px] font-semibold text-[#a1a1aa]">Password</label>
                                </div>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                           id="modal-login-pwd"
                                           name="password" 
                                           required 
                                           placeholder="••••••••" 
                                           class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] focus:ring-1 focus:ring-[#3291ff] outline-none transition">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#71717a] hover:text-white text-xs">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full mt-2 py-2.5 rounded-full bg-white hover:bg-[#e4e4e7] text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm">
                                Masuk ke Akun
                            </button>
                        </form>

                        <div class="pt-3 border-t border-[#18181b] flex items-center justify-between text-xs">
                            <span class="text-[#71717a]">Belum punya akun?</span>
                            <button type="button" @click="tab = 'register'" class="font-bold text-[#3291ff] hover:underline">
                                Buat Akun Baru &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: REGISTER FORM -->
                    <div x-show="tab === 'register'" x-cloak x-transition:enter="transition duration-150 ease-out" class="space-y-3">
                        <form method="POST" action="{{ route('register') }}" class="space-y-2.5">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label for="modal-reg-name" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Nama Lengkap</label>
                                    <input type="text" 
                                           id="modal-reg-name"
                                           name="name" 
                                           required 
                                           placeholder="Rizky Pratama" 
                                           class="w-full px-3 py-2 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] outline-none transition">
                                </div>
                                <div>
                                    <label for="modal-reg-username" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Username</label>
                                    <input type="text" 
                                           id="modal-reg-username"
                                           name="username" 
                                           required 
                                           placeholder="rizkydev" 
                                           class="w-full px-3 py-2 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] outline-none transition">
                                </div>
                            </div>

                            <div>
                                <label for="modal-reg-email" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Email</label>
                                <input type="email" 
                                       id="modal-reg-email"
                                       name="email" 
                                       required 
                                       placeholder="rizky@example.com" 
                                       class="w-full px-3 py-2 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] outline-none transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label for="modal-reg-pwd" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Password</label>
                                    <input type="password" 
                                           id="modal-reg-pwd"
                                           name="password" 
                                           required 
                                           placeholder="Min. 8 karakter" 
                                           class="w-full px-3 py-2 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] outline-none transition">
                                </div>
                                <div>
                                    <label for="modal-reg-pwd-conf" class="block text-[11px] font-semibold text-[#a1a1aa] mb-1">Konfirmasi Password</label>
                                    <input type="password" 
                                           id="modal-reg-pwd-conf"
                                           name="password_confirmation" 
                                           required 
                                           placeholder="Ulangi password" 
                                           class="w-full px-3 py-2 rounded-xl bg-[#09090b] border border-[#27272a] text-white text-xs placeholder-[#52525b] focus:border-[#3291ff] outline-none transition">
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full mt-2 py-2.5 rounded-full bg-white hover:bg-[#e4e4e7] text-black font-bold text-xs transition duration-150 active:scale-[0.99] shadow-sm">
                                Buat Akun & Bergabung
                            </button>
                        </form>

                        <div class="pt-2.5 border-t border-[#18181b] flex items-center justify-between text-xs">
                            <span class="text-[#71717a]">Sudah punya akun?</span>
                            <button type="button" @click="tab = 'login'" class="font-bold text-[#3291ff] hover:underline">
                                Masuk ke Akun &rarr;
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Terms footnote -->
                <p class="text-[10px] text-[#52525b] leading-tight">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan dan Kebijakan Privasi NampungYuk.
                </p>

            </div>

            <!-- RIGHT COLUMN: Giant NampungYuk Outline Watermark (X-Style, 5 cols) -->
            <div class="hidden lg:flex lg:col-span-5 relative bg-[#050505] border-l border-[#1f1f23] p-8 flex-col justify-between overflow-hidden select-none">
                
                <!-- Background Subtle Glow Grid -->
                <div class="absolute inset-0 bg-[radial-gradient(#3291ff_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
                <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#0070f3]/15 blur-3xl pointer-events-none"></div>

                <!-- Giant Watermark Logo Motif (Geometric Outline like X) -->
                <div class="absolute inset-0 flex items-center justify-center opacity-25 hover:opacity-35 transition duration-500 pointer-events-none">
                    <svg class="w-[125%] h-[125%] text-[#3291ff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round">
                        {{-- Open Tray Container --}}
                        <path d="M3 10.5 4.6 5.4A1.5 1.5 0 0 1 6 4.35h12a1.5 1.5 0 0 1 1.4 1.05L21 10.5" />
                        <path d="M3 10.5h4.5l1.2 2.1h6.6l1.2-2.1H21v6.9a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4z" />
                        {{-- Drop-in Arrow --}}
                        <path d="M12 1.8v4.2" stroke-width="1.2" />
                        <path d="M10.4 4.4 12 6l1.6-1.6" stroke-width="1.2" />
                    </svg>
                </div>

                <!-- Top feature tag -->
                <div class="relative z-10 space-y-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[11px] text-[#a1a1aa] font-medium backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Showroom Developer Indonesia</span>
                    </div>
                    <h3 class="text-lg font-bold text-white tracking-tight leading-snug">
                        Tempat karya codinganmu <br><span class="ny-gradient-text">mendapatkan panggung.</span>
                    </h3>
                </div>

                <!-- Bottom Floating Stats & QR Box (Inspired by X QR Widget) -->
                <div class="relative z-10 space-y-3 pt-12">
                    
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#a1a1aa]">
                            <svg class="w-3.5 h-3.5 text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Pamerkan link live demo & source code</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-[#a1a1aa]">
                            <svg class="w-3.5 h-3.5 text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Dapatkan upvote, apresiasi, & masukan</span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-[#a1a1aa]">
                            <svg class="w-3.5 h-3.5 text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Gabung diskusi komunitas teknologi</span>
                        </div>
                    </div>

                    <!-- Mini X-style QR Badge in Corner -->
                    <div class="p-3 rounded-2xl bg-[#09090b]/90 border border-[#27272a] backdrop-blur-md flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white p-1 shrink-0 flex items-center justify-center">
                            {{-- Geometric QR Code Visual Mockup --}}
                            <svg class="w-full h-full text-black" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14 0h4v4h-4v-4zm-4 4h4v4h-4v-4zm4-4h4v-4h-4v4z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-white leading-none">NampungYuk App</p>
                            <p class="text-[10px] text-[#71717a] mt-0.5 leading-tight">Buka di ponsel pintar untuk pengalaman showcase mobile.</p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
