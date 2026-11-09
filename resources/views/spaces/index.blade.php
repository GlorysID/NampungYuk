@extends('layouts.app')

@section('title', 'Spaces — NampungYuk')
@section('header_title', 'Spaces')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ createOpen: false }">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] text-xs font-semibold mb-1.5">
                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                <span>Ruang Audio & Video</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--primary)] tracking-tight">Spaces</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Ruang live audio dan video untuk berdiskusi langsung bareng developer lain.</p>
        </div>
        @auth
            <button type="button" @click="createOpen = true" class="btn-primary text-sm py-2.5 px-4.5 inline-flex items-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Mulai Space</span>
            </button>
        @else
            <button type="button" @click="window.openAuthModal('login')" class="btn-primary text-sm py-2.5 px-4.5 inline-flex items-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Mulai Space</span>
            </button>
        @endauth
    </div>

    <!-- Live spaces -->
    <div class="space-y-3.5">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Sedang Live</h2>
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
        </div>

        @forelse($live as $s)
            <a href="{{ route('spaces.show', $s->slug) }}" class="bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-5 flex items-center gap-4 shadow-xs transition-colors group">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <div class="min-w-0 flex-1 space-y-1">
                    <p class="font-bold text-base text-[var(--primary)] group-hover:text-[var(--primary-hover)] transition truncate">{{ $s->title }}</p>
                    <div class="flex items-center gap-2 text-xs text-[var(--text-muted)]">
                        @if($s->type === 'video')
                            <span class="inline-flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Video</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                                <span>Audio</span>
                            </span>
                        @endif
                        <span>·</span>
                        <span>Host: {{ $s->host?->name }}</span>
                    </div>
                </div>
                <span class="btn-primary text-sm py-2 px-4 shrink-0">Gabung</span>
            </a>
        @empty
            <x-empty-state title="Belum ada Space live" description="Mulai Space pertama untuk ngobrol bareng developer lain secara langsung." />
        @endforelse
    </div>

    <!-- Recent / Ended Spaces -->
    @if($recent->count())
        <div class="space-y-3 pt-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Selesai</h2>
            <div class="space-y-2">
                @foreach($recent as $s)
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-3.5 flex items-center justify-between gap-3 shadow-xs opacity-75">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-xs text-[var(--primary)] truncate">{{ $s->title }}</p>
                            <p class="text-[10px] text-[var(--text-muted)] mt-0.5">{{ $s->ended_at?->diffForHumans() }} · Host: {{ $s->host?->name }}</p>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0">Selesai</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Create modal -->
    @auth
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" @click.self="createOpen = false">
            <form method="POST" action="{{ route('spaces.store') }}" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 space-y-4 w-full max-w-md shadow-xl text-left">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-[var(--primary)]">Mulai Space Baru</h3>
                    <button type="button" @click="createOpen = false" class="text-[#908F88] hover:text-[var(--primary)] transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Judul Space</label>
                    <input type="text" name="title" required maxlength="80" class="ny-input text-sm" placeholder="mis. Ngobrol soal arsitektur backend & database">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Tipe Ruang</label>
                    <select name="type" class="ny-input text-sm">
                        <option value="audio">Audio Saja</option>
                        <option value="video">Audio & Video</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-2 px-4 cursor-pointer">Mulai Space</button>
                </div>
            </form>
        </div>
    @endauth

</div>
@endsection
