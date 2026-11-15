@extends('layouts.app')

@section('title', 'Ubah Alamat Email — NampungYuk')
@section('header_title', 'Ubah Alamat Email')
@section('header_subtitle', '@' . $user->username)
@section('main_width', 'max-w-none')

@section('content')
    <div class="max-w-2xl mx-auto space-y-5">

        <!-- Back link -->
        <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="inline-flex items-center gap-1.5 text-[13px] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
            <span>&larr;</span>
            <span>Keamanan &amp; Sesi</span>
        </a>

        <!-- Current Email -->
        <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[var(--primary)]">Email Saat Ini</h2>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Alamat yang sedang dipakai untuk masuk dan menerima notifikasi.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium shrink-0 {{ $user->hasVerifiedEmail() ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $user->hasVerifiedEmail() ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                    {{ $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                </span>
            </div>
            <div class="px-5 py-4">
                <p class="text-sm font-mono text-[var(--primary)] truncate">{{ $user->email }}</p>
            </div>
        </section>

        <!-- New Email Form -->
        <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[var(--primary)]">Ganti ke Email Baru</h2>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Kami kirim kode 6 digit ke alamat baru. Email lama tetap aktif sampai kode itu diverifikasi.</p>
                </div>
                <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)] shrink-0">Langkah 1 / 2</span>
            </div>

            <form method="POST" action="{{ route('settings.email.send') }}" class="p-5 space-y-4">
                @csrf

                @if (session('status'))
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[13px] flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label for="email" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)]">EMAIL BARU</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autocomplete="email"
                           placeholder="alamat-baru@email.com"
                           class="ny-input text-sm w-full">
                    @error('email') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                </div>

                <div class="p-3.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[12px] text-[var(--text-muted)] leading-relaxed">
                        Pastikan kamu bisa membuka kotak masuk alamat baru. Tanpa kode yang benar, email akunmu tidak berubah.
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-between gap-3 border-t border-[var(--border-color)]">
                    <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">Batal</a>
                    <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">Kirim Kode Verifikasi</button>
                </div>
            </form>
        </section>
    </div>
@endsection