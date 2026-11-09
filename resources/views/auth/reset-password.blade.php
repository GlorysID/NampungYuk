<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — NampungYuk</title>
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-dark.png') }}">
    <meta name="description" content="Perbarui kata sandi akun NampungYuk kamu dengan aman.">

    <!-- Fonts: Geist + Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--bg-app)] text-[var(--primary)] min-h-screen antialiased selection:bg-[var(--primary)] selection:text-[var(--primary-text)]">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2" x-data="{ showPassword: false }">

        <!-- LEFT HALF: Developer Showcase Panel (Full-bleed 50vw) -->
        <div class="hidden lg:flex bg-[var(--bg-app)] text-[var(--primary)] relative overflow-hidden flex-col justify-between p-12 lg:p-16 min-h-screen select-none border-r border-[var(--border-color)]">
            <!-- Subtle Neutral Mesh -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(11,11,13,0.03),transparent_70%)] dark:bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(234,232,225,0.03),transparent_70%)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px),linear-gradient(to_bottom,#00000008_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

            <!-- Top-Left Brand Header -->
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 font-bold text-[17px] tracking-tight relative z-10 hover:opacity-90 transition">
                <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-7 h-7 object-contain block dark:hidden">
                <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-7 h-7 object-contain hidden dark:block">
                <span class="text-[var(--primary)] font-extrabold">Nampung<span class="text-[var(--text-muted)]">Yuk</span></span>
                <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold">Dev Showcase</span>
            </a>

            <!-- Middle Display Headline & Value Proposition -->
            <div class="my-auto py-10 relative z-10 max-w-[480px]">
                <h2 class="text-4xl lg:text-[44px] font-extrabold tracking-[-0.03em] leading-[1.08] text-[var(--primary)]">
                    Kata sandi baru,<br>langkah baru.
                </h2>
                <p class="text-[15.5px] leading-relaxed text-[var(--text-muted)] mt-4 font-normal">
                    Buat kata sandi baru yang kuat dan unik agar akun serta karya codinganmu selalu terlindungi.
                </p>

                <!-- Sleek Terminal Window Card -->
                <div class="relative mt-7 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-sm overflow-hidden font-mono text-[12.5px]">
                    <div class="flex items-center justify-between px-4 py-2.5 bg-[var(--surface-muted)] border-b border-[var(--border-color)]">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] font-medium tracking-tight flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>key.rotation.ts</span>
                        </div>
                        <span class="text-[10px] text-[var(--text-muted)] bg-[var(--surface-muted)] border border-[var(--border-color)] px-1.5 py-0.5 rounded font-mono font-medium">KEY</span>
                    </div>
                    <div class="p-4 leading-relaxed text-[var(--primary-hover)] space-y-1">
                        <div><span class="text-violet-600 dark:text-violet-400 font-medium">const</span> <span class="text-blue-600 dark:text-blue-400 font-medium">credentials</span> = {</div>
                        <div class="pl-4"><span class="text-[var(--text-muted)]">hashAlgorithm:</span> <span class="text-emerald-600 dark:text-emerald-400">"bcrypt-cost-12"</span>,</div>
                        <div class="pl-4"><span class="text-[var(--text-muted)]">minEntropy:</span> <span class="text-amber-600 dark:text-amber-400">80</span>,</div>
                        <div class="pl-4 flex items-center gap-2">
                            <span class="text-[var(--text-muted)]">status:</span> <span class="text-emerald-600 dark:text-emerald-400">"validating"</span>,
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 font-sans font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Strong Key
                            </span>
                        </div>
                        <div>};</div>
                    </div>
                </div>

                <!-- 3 Sharp Metric Columns -->
                <div class="grid grid-cols-3 gap-6 pt-7 border-t border-[var(--border-color)] mt-8">
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[var(--primary)] leading-none">1.2K+</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[var(--text-muted)] mt-2 font-semibold">KARYA TERPAMER</div>
                    </div>
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[var(--primary)] leading-none">40+</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[var(--text-muted)] mt-2 font-semibold">KOMUNITAS</div>
                    </div>
                    <div>
                        <div class="text-2xl lg:text-[26px] font-extrabold tracking-tight text-[var(--primary)] leading-none">100%</div>
                        <div class="text-[10.5px] font-mono uppercase tracking-[0.08em] text-[var(--text-muted)] mt-2 font-semibold">GRATIS &amp; TERBUKA</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Footer Metatag -->
            <div class="text-[11px] font-mono uppercase tracking-[0.16em] text-[var(--text-muted)] relative z-10">
                KEAMANAN &middot; AKUN &middot; NAMPUNGYUK
            </div>
        </div>

        <!-- RIGHT HALF: Form Surface (Full-bleed 50vw) -->
        <div class="w-full min-h-screen bg-[var(--surface)] text-[var(--primary)] flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-16 relative overflow-y-auto">

            <!-- Top Navigation Bar -->
            <div class="absolute top-6 left-6 right-6 sm:top-8 sm:left-12 sm:right-12 flex items-center justify-between z-20">
                <a href="{{ url('/') }}" class="lg:hidden inline-flex items-center gap-2 font-bold text-base text-[var(--primary)] tracking-tight">
                    <img src="{{ asset('images/lightmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain block dark:hidden">
                    <img src="{{ asset('images/darkmode-icon.png') }}" alt="NampungYuk" class="w-5 h-5 object-contain hidden dark:block">
                    <span>Nampung<span class="text-[var(--text-muted)]">Yuk</span></span>
                </a>
                <div class="hidden lg:block"></div>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                    <span>&larr;</span>
                    <span>Kembali ke Masuk</span>
                </a>
            </div>

            <!-- Inner Form Wrapper -->
            <div class="w-full max-w-[420px] mx-auto my-auto py-12">

                <!-- Header -->
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-bold tracking-tight text-[var(--primary)] leading-tight">
                        Buat password baru
                    </h1>
                    <p class="text-[15px] text-[var(--text-muted)] mt-1.5">
                        Masukkan password baru yang aman untuk akunmu.
                    </p>
                </div>

                <!-- Reset Form -->
                <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)] mb-1.5">ALAMAT EMAIL</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $email) }}" 
                               required 
                               readonly 
                               class="w-full h-12 px-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] text-[15px] text-[var(--text-muted)] opacity-80 cursor-not-allowed outline-none font-mono">
                        @error('email')
                            <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)] mb-1.5">PASSWORD BARU</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autofocus 
                                   placeholder="Min. 8 karakter" 
                                   class="w-full h-12 px-4 pr-11 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus:border-[var(--primary)] text-[15px] text-[var(--primary)] placeholder:text-[var(--text-muted)] outline-none transition @error('password') border-rose-500/80 focus:border-rose-500 @enderror">
                            <button type="button" @click="showPassword = !showPassword" aria-label="Tampilkan atau sembunyikan password" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)] mb-1.5">KONFIRMASI PASSWORD BARU</label>
                        <input :type="showPassword ? 'text' : 'password'" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required 
                               placeholder="Ulangi kata sandi baru" 
                               class="w-full h-12 px-4 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus:border-[var(--primary)] text-[15px] text-[var(--primary)] placeholder:text-[var(--text-muted)] outline-none transition">
                    </div>

                    <button type="submit" 
                            class="w-full h-12 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] font-bold text-[15px] transition shadow-xs active:scale-[0.99] cursor-pointer mt-2">
                        Simpan Password Baru
                    </button>
                </form>

            </div>

        </div>

    </div>

</body>
</html>
