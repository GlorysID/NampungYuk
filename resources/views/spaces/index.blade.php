@extends('layouts.app')

@section('title', 'Spaces — NampungYuk')

@section('content')
<div class="space-y-5 max-w-4xl mx-auto" x-data="{ createOpen: false }">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Spaces</h1>
            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Ruang live audio/video untuk ngobrol bareng developer.</p>
        </div>
        @auth
            <button type="button" @click="createOpen = true" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Mulai Space</span>
            </button>
        @endauth
    </div>

    <!-- Live spaces -->
    <div class="space-y-3">
        <h2 class="hl-label">Sedang Live</h2>
        @forelse($live as $s)
            <a href="{{ route('spaces.show', $s->slug) }}" class="ny-card p-4 flex items-center gap-3 hover:border-[#0070f3] dark:hover:border-[#3291ff] transition">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] truncate">{{ $s->title }}</p>
                    <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">
                        {{ $s->type === 'video' ? '📹 Video' : '🎙️ Audio' }} · oleh {{ $s->host?->name }}
                    </p>
                </div>
                <span class="btn-secondary text-xs py-1.5 px-3 shrink-0">Gabung</span>
            </a>
        @empty
            <x-empty-state title="Belum ada Space live" description="Mulai Space pertama untuk ngobrol bareng developer lain." />
        @endforelse
    </div>

    <!-- Recent -->
    @if($recent->count())
        <div class="space-y-3">
            <h2 class="hl-label">Selesai</h2>
            @foreach($recent as $s)
                <div class="ny-card p-3.5 flex items-center gap-3 opacity-70">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] truncate">{{ $s->title }}</p>
                        <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]">{{ $s->ended_at?->diffForHumans() }} · {{ $s->host?->name }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create modal -->
    @auth
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/55 backdrop-blur-xs" @click.self="createOpen = false">
            <form method="POST" action="{{ route('spaces.store') }}" class="ny-card bg-white dark:bg-[#0a0a0a] p-5 space-y-4 w-full max-w-md">
                @csrf
                <h3 class="font-bold text-sm text-[#18181b] dark:text-[#fafafa]">Mulai Space Baru</h3>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Judul</label>
                    <input type="text" name="title" required maxlength="80" class="ny-input text-sm" placeholder="mis. Ngobrol soal karier backend">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Tipe</label>
                    <select name="type" class="ny-input text-sm">
                        <option value="audio">🎙️ Audio</option>
                        <option value="video">📹 Video</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="createOpen = false" class="btn-secondary text-xs py-2 px-4">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-2 px-4">Mulai</button>
                </div>
            </form>
        </div>
    @endauth

</div>
@endsection
