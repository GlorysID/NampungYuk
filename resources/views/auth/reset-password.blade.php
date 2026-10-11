<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — NampungYuk</title>
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
            <span>Kembali ke Masuk</span>
        </a>
    </div>

    <div class="w-full max-w-md mx-auto min-h-0 flex items-center justify-center">
        <div class="w-full ny-card px-6 py-6 sm:px-7 sm:py-7 space-y-5 bg-white dark:bg-[#0a0a0a] max-h-full overflow-y-auto">
            <div class="text-center space-y-2">
                <x-logo size="lg" :show-tagline="true" class="justify-center" />
                <div class="space-y-0.5">
                    <h1 class="text-base font-bold text-[#18181b] dark:text-[#fafafa]">Buat Password Baru</h1>
                    <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">Masukkan password baru untuk akunmu.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-1">
                    <label for="email" class="flex items-center gap-1.5 font-semibold text-[#18181b] dark:text-[#fafafa]">
                        <svg class="w-3.5 h-3.5 text-[#63636b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Alamat Email</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required readonly class="ny-input text-xs sm:text-sm opacity-70">
                    @error('email') <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="password" class="flex items-center gap-1.5 font-semibold text-[#18181b] dark:text-[#fafafa]">
                        <svg class="w-3.5 h-3.5 text-[#63636b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Password Baru (Minimal 8 karakter)</span>
                    </label>
                    <input type="password" id="password" name="password" required autofocus placeholder="••••••••" class="ny-input text-xs sm:text-sm">
                    @error('password') <p class="text-rose-500 font-medium text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="password_confirmation" class="flex items-center gap-1.5 font-semibold text-[#18181b] dark:text-[#fafafa]">
                        <svg class="w-3.5 h-3.5 text-[#63636b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Konfirmasi Password Baru</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" class="ny-input text-xs sm:text-sm">
                </div>

                <button type="submit" class="btn-primary w-full py-2.5 text-xs sm:text-sm font-bold gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Password Baru</span>
                </button>
            </form>
        </div>
    </div>

    <div class="w-full max-w-md mx-auto text-center text-[11px] text-[#63636b] dark:text-[#a0a0a0]">
        <span>NampungYuk &copy; {{ date('Y') }} &bull; Platform Komunitas Developer</span>
    </div>

</body>
</html>
