<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Email — NampungYuk</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#e9e9ec] dark:bg-[#000000] text-[#18181b] dark:text-[#fafafa] h-dvh overflow-hidden grid grid-rows-[auto_1fr_auto] gap-2 p-4 sm:p-5 antialiased selection:bg-[#3291ff]/25" x-data>

    <div class="w-full max-w-md mx-auto flex items-center justify-between text-xs">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Halaman Masuk</span>
        </a>
    </div>

    <div class="w-full max-w-md mx-auto min-h-0 flex items-center justify-center">
        <div class="w-full ny-card px-6 py-6 sm:px-7 sm:py-7 space-y-5 bg-white dark:bg-[#0a0a0a] max-h-full overflow-y-auto">
            
            <div class="text-center space-y-2.5">
                <x-logo size="lg" :show-tagline="true" class="justify-center" />
                
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>

                <div class="space-y-1">
                    <h1 class="text-base font-bold text-[#18181b] dark:text-[#fafafa]">Cek Kotak Masuk Email Kamu</h1>
                    <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] leading-relaxed">
                        Kami telah mengirimkan tautan verifikasi ke
                        @if (!empty($email))
                            <span class="font-semibold text-[#18181b] dark:text-white">{{ $email }}</span>.
                        @else
                            alamat email kamu.
                        @endif
                        Silakan klik tautan di dalam email tersebut untuk mengaktifkan akunmu.
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-[#e6f0ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 text-[#0070f3] dark:text-[#47a8ff] text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-500 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pt-2 border-t border-[#e4e4e7] dark:border-[#27272a] space-y-3">
                <p class="text-[11px] text-[#71717a] text-center">
                    Belum menerima email verifikasi? Cek folder Spam atau minta tautan baru di bawah ini.
                </p>

                <form method="POST" action="{{ route('verification.resend') }}" class="space-y-2">
                    @csrf
                    @if (!empty($email))
                        <input type="hidden" name="email" value="{{ $email }}">
                    @else
                        <div class="space-y-1">
                            <input type="email" name="email" required placeholder="Masukkan email kamu" class="ny-input text-xs">
                        </div>
                    @endif

                    <button type="submit" class="w-full btn-primary text-xs py-2.5">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="w-full max-w-md mx-auto text-center text-[10px] text-[#71717a]">
        &copy; {{ date('Y') }} NampungYuk &middot; Wadah Karya Programmer Nusantara
    </div>

</body>
</html>
