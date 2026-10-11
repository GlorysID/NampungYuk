<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — NampungYuk</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') { document.documentElement.classList.remove('dark'); }
            else { document.documentElement.classList.add('dark'); }
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
            <span>Kembali ke Masuk</span>
        </a>
    </div>

    <div class="w-full max-w-md mx-auto min-h-0 flex items-center justify-center">
        <div class="w-full ny-card px-6 py-6 sm:px-7 sm:py-7 space-y-5 bg-white dark:bg-[#0a0a0a] max-h-full overflow-y-auto">
            <div class="text-center space-y-2">
                <x-logo size="lg" :show-tagline="true" class="justify-center" />
                <div class="space-y-0.5">
                    <h1 class="text-base font-bold text-[#18181b] dark:text-[#fafafa]">Lupa Password?</h1>
                    <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">Masukkan emailmu, kami kirim tautan untuk membuat password baru.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="p-3 rounded-lg bg-[#e6f0ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 text-[#0070f3] dark:text-[#47a8ff] text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4 text-xs">
                @csrf
                <div class="space-y-1">
                    <label for="email" class="flex items-center gap-1.5 font-semibold text-[#18181b] dark:text-[#fafafa]">
                        <svg class="w-3.5 h-3.5 text-[#63636b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Alamat Email</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" class="ny-input text-xs sm:text-sm">
                    @error('email') <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-2.5 text-xs sm:text-sm font-bold gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Kirim Tautan Reset</span>
                </button>
            </form>

            <div class="text-center text-xs text-[#63636b] dark:text-[#a0a0a0] border-t border-[#e4e4e7] dark:border-[#1f1f1f] pt-4">
                <p>Ingat passwordmu? <a href="{{ route('login') }}" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">Masuk di sini</a></p>
            </div>
        </div>
    </div>

    <div class="w-full max-w-md mx-auto text-center text-[11px] text-[#63636b] dark:text-[#a0a0a0]">
        <span>NampungYuk &copy; {{ date('Y') }} &bull; Platform Komunitas Developer</span>
    </div>

</body>
</html>
