<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Email — NampungYuk</title>
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-dark.png') }}">
    <meta name="description" content="Verifikasi alamat email kamu untuk mengaktifkan akun NampungYuk.">

    <!-- Fonts: Geist + Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--bg-app)] text-[var(--primary)] min-h-screen antialiased selection:bg-[var(--primary)] selection:text-[var(--primary-text)]">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

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
                    Satu langkah lagi,<br>aktifkan akunmu.
                </h2>
                <p class="text-[15.5px] leading-relaxed text-[var(--text-muted)] mt-4 font-normal">
                    Verifikasi email memastikan akunmu aman dan siap menerima notifikasi, feedback karya, serta pesan dari komunitas.
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
                            <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>email.verification.ts</span>
                        </div>
                        <span class="text-[10px] text-[var(--text-muted)] bg-[var(--surface-muted)] border border-[var(--border-color)] px-1.5 py-0.5 rounded font-mono font-medium">MAIL</span>
                    </div>
                    <div class="p-4 leading-relaxed text-[var(--primary-hover)] space-y-1">
                        <div><span class="text-violet-600 dark:text-violet-400 font-medium">const</span> <span class="text-blue-600 dark:text-blue-400 font-medium">userStatus</span> = {</div>
                        <div class="pl-4"><span class="text-[var(--text-muted)]">emailVerified:</span> <span class="text-amber-600 dark:text-amber-400">false</span>,</div>
                        <div class="pl-4"><span class="text-[var(--text-muted)]">communityReady:</span> <span class="text-emerald-600 dark:text-emerald-400">true</span>,</div>
                        <div class="pl-4 flex items-center gap-2">
                            <span class="text-[var(--text-muted)]">action:</span> <span class="text-emerald-600 dark:text-emerald-400">"awaiting-click"</span>,
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 font-sans font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Pending
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
                AKTIVASI &middot; AKUN &middot; NAMPUNGYUK
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
                    <span>Kembali ke Halaman Masuk</span>
                </a>
            </div>

            <!-- Inner Form Wrapper -->
            <div class="w-full max-w-[420px] mx-auto my-auto py-12">

                <!-- Mail Badge Icon -->
                <div class="w-12 h-12 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>

                <!-- Header -->
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-bold tracking-tight text-[var(--primary)] leading-tight">
                        Cek kotak masuk email kamu
                    </h1>
                    <p class="text-[15px] text-[var(--text-muted)] mt-2 leading-relaxed">
                        Kami telah mengirimkan tautan verifikasi ke
                        @if (!empty($email))
                            <span class="font-semibold text-[var(--primary)]">{{ $email }}</span>.
                        @else
                            alamat email kamu.
                        @endif
                        Silakan klik tautan di dalam email tersebut untuk mengaktifkan akunmu.
                    </p>
                </div>

                <!-- Status alert if resent -->
                @if (session('status'))
                    <div class="mt-6 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-[13px] flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-500/20 text-rose-800 dark:text-rose-300 text-[13px]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Resend Form -->
                <div class="mt-8 pt-6 border-t border-[var(--border-color)]">
                    <p class="text-[13px] text-[var(--text-muted)] mb-4 leading-normal">
                        Belum menerima email verifikasi? Cek folder Spam atau minta tautan baru di bawah ini.
                    </p>

                    <form method="POST" action="{{ route('verification.resend') }}" class="space-y-4">
                        @csrf
                        @if (!empty($email))
                            <input type="hidden" name="email" value="{{ $email }}">
                        @else
                            <div>
                                <label for="email" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)] mb-1.5">ALAMAT EMAIL</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       placeholder="nama@email.com" 
                                       class="w-full h-12 px-4 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus:border-[var(--primary)] text-[15px] text-[var(--primary)] placeholder:text-[var(--text-muted)] outline-none transition">
                            </div>
                        @endif

                        <button type="submit" 
                                class="w-full h-12 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] font-bold text-[15px] transition shadow-xs active:scale-[0.99] cursor-pointer">
                            Kirim Ulang Email Verifikasi
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
