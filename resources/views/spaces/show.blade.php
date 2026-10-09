@extends('layouts.app')

@section('title', $space->title . ' — Space NampungYuk')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <a href="{{ route('spaces.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Spaces</span>
    </a>

    <div class="ny-card p-5 space-y-4"
         x-data="spaceRoom({
             spaceId: {{ $space->id }},
             me: {{ auth()->id() }},
             type: '{{ $space->type }}',
             signalUrl: '{{ route('spaces.signal', $space) }}',
             isHost: {{ auth()->id() === $space->host_id ? 'true' : 'false' }}
         })" x-init="init()">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                    </span>
                    <h1 class="font-bold text-base text-[#18181b] dark:text-[#fafafa] truncate">{{ $space->title }}</h1>
                </div>
                <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] mt-0.5">{{ $space->type === 'video' ? 'Video Space' : 'Audio Space' }} · Host: {{ $space->host?->name }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="!joined">
                    <button type="button" @click="join()" class="btn-primary text-xs py-2 px-4">Gabung</button>
                </template>
                <template x-if="joined">
                    <button type="button" @click="leave()" class="btn-secondary text-xs py-2 px-4">Keluar</button>
                </template>
                @if(auth()->id() === $space->host_id)
                    <form method="POST" action="{{ route('spaces.end', $space) }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-600 px-2">Akhiri</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Video grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" x-show="type === 'video'">
            <!-- Local -->
            <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                <video x-ref="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <span class="absolute bottom-1.5 left-1.5 text-[10px] font-semibold text-white bg-black/50 px-1.5 py-0.5 rounded">Kamu</span>
            </div>
            <!-- Remotes -->
            <template x-for="p in peers" :key="p.id">
                <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <video :id="'peer-' + p.id" autoplay playsinline class="w-full h-full object-cover"></video>
                    <span class="absolute bottom-1.5 left-1.5 text-[10px] font-semibold text-white bg-black/50 px-1.5 py-0.5 rounded" x-text="p.name"></span>
                </div>
            </template>
        </div>

        <!-- Audio-only participant list -->
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" x-show="type === 'audio'">
            <template x-for="p in peers" :key="p.id">
                <div class="flex flex-col items-center gap-1.5">
                    <div class="w-14 h-14 rounded-full ny-gradient-bg text-white flex items-center justify-center font-bold text-lg" x-text="p.name ? p.name[0] : '?'"></div>
                    <span class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate max-w-full" x-text="p.name"></span>
                </div>
            </template>
            <p x-show="peers.length === 0" class="col-span-full text-center text-xs text-[#63636b] dark:text-[#a0a0a0] py-4">
                Belum ada peserta lain. Bagikan room ini ke temanmu.
            </p>
        </div>

        <!-- Controls -->
        <div class="flex items-center justify-center gap-2 pt-1 border-t border-[#e4e4e7] dark:border-[#1f1f1f]" x-show="joined" x-cloak>
            <button type="button" @click="toggleMic()" :class="micOn ? 'bg-[#0070f3] text-white' : 'bg-rose-100 dark:bg-rose-950/40 text-rose-600'" class="w-10 h-10 rounded-full flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
            </button>
            <template x-if="type === 'video'">
                <button type="button" @click="toggleCam()" :class="camOn ? 'bg-[#0070f3] text-white' : 'bg-rose-100 dark:bg-rose-950/40 text-rose-600'" class="w-10 h-10 rounded-full flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </template>
        </div>
    </div>

</div>
@endsection
