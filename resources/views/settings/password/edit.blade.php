@extends('layouts.app')

@section('title', $hasPassword ? 'Ubah Password — NampungYuk' : 'Buat Password — NampungYuk')
@section('header_title', $hasPassword ? 'Ubah Password' : 'Buat Password')
@section('header_subtitle', '@' . $user->username)
@section('main_width', 'max-w-none')

@section('content')
    <div class="max-w-2xl mx-auto space-y-5">

        <!-- Back link -->
        <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="inline-flex items-center gap-1.5 text-[13px] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
            <span>&larr;</span>
            <span>Keamanan &amp; Sesi</span>
        </a>

        <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
            @if ($needsCurrentPassword)
                <!-- STEP 1 / 2: confirm current password -->
                <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-[var(--primary)]">Konfirmasi Password Saat Ini</h2>
                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Masukkan password yang sekarang dipakai untuk membuka formulir password baru.</p>
                    </div>
                    <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-[var(--primary)] text-[var(--primary-text)] shrink-0">Langkah 1 / 2</span>
                </div>

                <!-- Step progress -->
                <div class="px-5 pt-4 flex items-center gap-2">
                    <span class="h-1 flex-1 rounded-full bg-[var(--primary)]"></span>
                    <span class="h-1 flex-1 rounded-full bg-[var(--border-color)]"></span>
                </div>

                <form method="POST" action="{{ route('settings.password.verify') }}" class="p-5 space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="current_password" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)]">PASSWORD SAAT INI</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               autocomplete="current-password"
                               required
                               autofocus
                               placeholder="Password yang sekarang"
                               class="ny-input text-sm w-full">
                        @error('current_password') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-3 border-t border-[var(--border-color)]">
                        <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">Batal</a>
                        <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">Lanjut ke Password Baru</button>
                    </div>
                </form>

                <!-- Escape hatch: reset via email code -->
                <div class="px-5 py-4 border-t border-[var(--border-color)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[var(--surface-subtle)]">
                    <p class="text-[12px] text-[var(--text-muted)]">Tidak ingat password saat ini?</p>
                    <form method="POST" action="{{ route('settings.password.forgot') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Lupa password saat ini?</button>
                    </form>
                </div>
            @else
                <!-- STEP 2 / 2 (or create for Google-only accounts) -->
                <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-[var(--primary)]">
                            {{ $hasPassword ? 'Password Baru' : 'Buat Password Akun' }}
                        </h2>
                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">
                            @if ($hasPassword)
                                Password saat ini sudah dikonfirmasi. Tentukan password baru untuk akunmu.
                            @else
                                Akun ini masuk lewat Google dan belum punya password. Buat satu supaya juga bisa masuk tanpa Google.
                            @endif
                        </p>
                    </div>
                    @if ($hasPassword)
                        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-[var(--primary)] text-[var(--primary-text)] shrink-0">Langkah 2 / 2</span>
                    @else
                        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)] shrink-0">Akun Google</span>
                    @endif
                </div>

                @if ($hasPassword)
                    <!-- Step progress -->
                    <div class="px-5 pt-4 flex items-center gap-2">
                        <span class="h-1 flex-1 rounded-full bg-[var(--primary)]"></span>
                        <span class="h-1 flex-1 rounded-full bg-[var(--primary)]"></span>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('settings.password.update') }}"
                      x-data="{ showNew: false, showConfirm: false }"
                      class="p-5 space-y-4">
                    @csrf

                    @if (session('status'))
                        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[13px] flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="password" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)]">PASSWORD BARU</label>
                            <div class="relative">
                                <input :type="showNew ? 'text' : 'password'"
                                       id="password"
                                       name="password"
                                       autocomplete="new-password"
                                       required
                                       minlength="8"
                                       placeholder="Minimal 8 karakter"
                                       class="ny-input text-sm w-full pr-10">
                                <button type="button"
                                        @click="showNew = !showNew"
                                        aria-label="Tampilkan atau sembunyikan password baru"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                                    <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showNew" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)]">KONFIRMASI PASSWORD BARU</label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       autocomplete="new-password"
                                       required
                                       minlength="8"
                                       placeholder="Ulangi password baru"
                                       class="ny-input text-sm w-full pr-10">
                                <button type="button"
                                        @click="showConfirm = !showConfirm"
                                        aria-label="Tampilkan atau sembunyikan konfirmasi password baru"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-3 border-t border-[var(--border-color)]">
                        <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">Batal</a>
                        <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">{{ $hasPassword ? 'Simpan Password Baru' : 'Buat Password' }}</button>
                    </div>
                </form>
            @endif
        </section>
    </div>
@endsection