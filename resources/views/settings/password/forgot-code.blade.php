@extends('layouts.app')

@section('title', 'Verifikasi Lupa Password — NampungYuk')
@section('header_title', 'Verifikasi Kode')
@section('header_subtitle', '@' . $user->username)
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
    <div class="max-w-2xl mx-auto space-y-5">

        <!-- Back link -->
        <a href="{{ route('settings.password.edit') }}" class="inline-flex items-center gap-1.5 text-[13px] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
            <span>&larr;</span>
            <span>Kembali ke ubah password</span>
        </a>

        <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[var(--primary)]">Masukkan Kode dari Email</h2>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">
                        Kode 6 digit dikirim ke <span class="font-mono text-[var(--primary)]">{{ $email }}</span>.
                    </p>
                </div>
                <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)] shrink-0">Verifikasi</span>
            </div>

            <div class="p-5 space-y-5">
                <!-- Info: what happens after verify -->
                <div class="p-3.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[12px] text-[var(--text-muted)] leading-relaxed">
                        Setelah kode ini terverifikasi, kamu langsung bisa menentukan password baru tanpa memasukkan password lama.
                    </p>
                </div>

                @if (session('status'))
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[13px] flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.password.forgot.verify') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="code" class="block text-[11px] font-mono font-bold uppercase tracking-[0.08em] text-[var(--text-muted)]">KODE 6 DIGIT</label>
                        <input type="text"
                               id="code"
                               name="code"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               required
                               autofocus
                               placeholder="______"
                               class="ny-input text-lg w-full font-mono tracking-[0.4em] text-center sm:max-w-xs">
                        @error('code') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-3 border-t border-[var(--border-color)]">
                        <a href="{{ route('settings.password.edit') }}" class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">Kembali</a>
                        <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">Verifikasi Kode</button>
                    </div>
                </form>

                <!-- Resend -->
                <div class="pt-4 border-t border-[var(--border-color)] flex items-center justify-between gap-3">
                    <p class="text-[12px] text-[var(--text-muted)]">Kode belum sampai?</p>
                    <form method="POST" action="{{ route('settings.password.forgot') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-[var(--primary)] hover:underline underline-offset-4 transition cursor-pointer">Kirim ulang kode</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection