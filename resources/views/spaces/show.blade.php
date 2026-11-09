@extends('layouts.app')

@section('title', $space->title . ' — Space NampungYuk')
@section('header_title', $space->title)
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    <a href="{{ route('spaces.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Spaces</span>
    </a>

    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 space-y-5 shadow-xs"
         x-data="spaceRoom({
             spaceId: {{ $space->id }},
             spaceSlug: '{{ $space->slug }}',
             me: {{ auth()->id() ?? 'null' }},
             type: '{{ $space->type }}',
             signalUrl: '{{ route('spaces.signal', $space) }}',
             isHost: {{ auth()->check() && (int) auth()->id() === (int) $space->host_id ? 'true' : 'false' }},
             isJoined: {{ auth()->check() && ($participants->contains('id', auth()->id()) || (int) auth()->id() === (int) $space->host_id) ? 'true' : 'false' }}
         })" x-init="init()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--border-color)]">
            <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2">
                    @if($space->status === 'ended')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] text-[10px] font-semibold uppercase tracking-wider">
                            Selesai
                        </span>
                    @else
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                    @endif
                    <h1 class="font-bold text-base sm:text-lg text-[var(--primary)] truncate">{{ $space->title }}</h1>
                </div>
                <p class="text-xs text-[var(--text-muted)]">
                    {{ $space->type === 'video' ? 'Video Space' : 'Audio Space' }} · Host: <strong class="text-[var(--primary)] font-semibold">{{ $space->host?->name }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($space->status === 'ended')
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                        Space Telah Berakhir
                    </span>
                @else
                    @guest
                        <button type="button" @click="window.openAuthModal('login')" class="btn-primary text-xs py-2 px-4 cursor-pointer shadow-xs">
                            Masuk untuk Gabung
                        </button>
                    @else
                        <template x-if="!joined">
                            <button type="button" @click="join()" class="btn-primary text-xs py-2 px-4 cursor-pointer shadow-xs">Gabung</button>
                        </template>
                        <template x-if="joined">
                            <button type="button" @click="leave()" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Keluar</button>
                        </template>
                        @if(auth()->id() === $space->host_id)
                            <form method="POST" action="{{ route('spaces.end', $space) }}" onsubmit="return confirm('Akhiri space ini untuk semua peserta?')">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-3 py-2 rounded-xl transition cursor-pointer">
                                    Akhiri Space
                                </button>
                            </form>
                        @endif
                    @endguest
                @endif
            </div>
        </div>

        <!-- Stage Viewport Canvas -->
        <div class="rounded-2xl p-4 sm:p-6 bg-[var(--bg-app)] border border-[var(--border-color)] space-y-4">
            <!-- Video grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" x-show="type === 'video'">
                <!-- Local preview -->
                <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0B0B0D] border border-[#242426]">
                    <video x-ref="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <span class="absolute bottom-2 left-2 text-[10px] font-semibold text-[#0B0B0D] bg-[#EAE8E1]/90 backdrop-blur-xs px-2 py-0.5 rounded-md">Kamu</span>
                </div>
                <!-- Remote Peers -->
                <template x-for="p in peers" :key="p.id">
                    <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0B0B0D] border border-[#242426]">
                        <video :id="'peer-' + p.id" autoplay playsinline class="w-full h-full object-cover"></video>
                        <span class="absolute bottom-2 left-2 text-[10px] font-semibold text-[#0B0B0D] bg-[#EAE8E1]/90 backdrop-blur-xs px-2 py-0.5 rounded-md" x-text="p.name"></span>
                    </div>
                </template>
            </div>

            <!-- Audio-only participant grid -->
            <div class="space-y-4" x-show="type === 'audio'">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    {{-- Host --}}
                    <div class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-center shadow-xs">
                        <div class="relative">
                            @if($space->host && $space->host->avatar)
                                <img src="{{ $space->host->avatar }}" alt="{{ $space->host->name }}" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-[var(--primary)]">
                            @else
                                <div class="w-14 h-14 rounded-2xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center font-bold text-lg ring-2 ring-[var(--primary)]">
                                    {{ mb_substr($space->host?->name ?? 'H', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 max-w-full space-y-0.5">
                            <p class="text-xs font-semibold text-[var(--primary)] truncate">{{ $space->host?->name }}</p>
                            <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[var(--primary)] text-[var(--primary-text)]">Host</span>
                        </div>
                    </div>

                    {{-- Connected WebRTC peers --}}
                    <template x-for="p in peers" :key="p.id">
                        <div class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-center shadow-xs">
                            <div class="w-14 h-14 rounded-2xl bg-[var(--surface-muted-flat)] text-[var(--primary)] flex items-center justify-center font-bold text-lg border border-[var(--border-color)]" x-text="p.name ? p.name[0] : '?'"></div>
                            <span class="text-xs font-medium text-[var(--primary-hover)] truncate max-w-full" x-text="p.name"></span>
                        </div>
                    </template>

                    {{-- Other Participants --}}
                    @foreach($participants->where('id', '!=', $space->host_id) as $part)
                        <div x-show="!peers.some(p => Number(p.id) === {{ (int) $part->id }})" class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-center shadow-xs">
                            @if($part->avatar)
                                <img src="{{ $part->avatar }}" alt="{{ $part->name }}" class="w-14 h-14 rounded-2xl object-cover border border-[var(--border-color)]">
                            @else
                                <div class="w-14 h-14 rounded-2xl bg-[var(--surface-muted-flat)] text-[var(--primary)] flex items-center justify-center font-bold text-lg border border-[var(--border-color)]">
                                    {{ mb_substr($part->name, 0, 1) }}
                                </div>
                            @endif
                            <span class="text-xs font-medium text-[var(--primary-hover)] truncate max-w-full">{{ $part->name }}</span>
                        </div>
                    @endforeach
                </div>

                @if($participants->isEmpty() && $space->status === 'live')
                    <p x-show="peers.length === 0" class="text-center text-xs text-[var(--text-muted)] py-6">
                        Belum ada peserta lain. Bagikan tautan ruang ini ke developer lain untuk mulai ngobrol.
                    </p>
                @endif
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center justify-center gap-3 pt-3 border-t border-[var(--border-color)]" x-show="joined" x-cloak>
            <button type="button" @click="toggleMic()" 
                    :class="micOn ? 'bg-[var(--primary)] text-[var(--primary-text)] shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)]'" 
                    class="w-11 h-11 rounded-full flex items-center justify-center transition cursor-pointer"
                    :title="micOn ? 'Matikan Mikrofon' : 'Nyalakan Mikrofon'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
            </button>
            <template x-if="type === 'video'">
                <button type="button" @click="toggleCam()" 
                        :class="camOn ? 'bg-[var(--primary)] text-[var(--primary-text)] shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)]'" 
                        class="w-11 h-11 rounded-full flex items-center justify-center transition cursor-pointer"
                        :title="camOn ? 'Matikan Kamera' : 'Nyalakan Kamera'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </template>
        </div>

        <!-- Space Chat Pane (Stone & Obsidian Aesthetic) -->
        <div class="pt-2 border-t border-[var(--border-color)]" x-data="{ chatOpen: true, spaceMsgs: [], draft: '' }">
            <div class="flex items-center justify-between pb-2">
                <button type="button" @click="chatOpen = !chatOpen" class="inline-flex items-center gap-2 text-xs font-bold text-[var(--primary)] hover:opacity-80 transition cursor-pointer">
                    <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>Obrolan Ruang (Space Chat)</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]" x-text="spaceMsgs.length"></span>
                </button>
                <button type="button" @click="chatOpen = !chatOpen" class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                    <span x-text="chatOpen ? 'Sembunyikan' : 'Buka'"></span>
                </button>
            </div>

            <div x-show="chatOpen" x-collapse class="space-y-3">
                <div class="rounded-xl p-3 max-h-48 overflow-y-auto space-y-2 bg-[var(--bg-app)] border border-[var(--border-color)]">
                    <template x-if="spaceMsgs.length === 0">
                        <div class="text-center py-4 text-xs text-[var(--text-muted)]">
                            Belum ada pesan di ruang ini. Mulai tinggalkan sapaan!
                        </div>
                    </template>
                    <template x-for="(msg, idx) in spaceMsgs" :key="idx">
                        <div class="flex" :class="msg.mine ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%] px-3 py-1.5 rounded-2xl text-xs leading-relaxed shadow-xs"
                                 :class="msg.mine ? 'bg-[#EAE8E1] text-[#0B0B0D] font-medium rounded-br-md' : 'bg-[#161619] text-[#EAE8E1] border border-[#242426] rounded-bl-md'">
                                <p class="text-[10px] font-bold opacity-75 mb-0.5" x-text="msg.name"></p>
                                <p x-text="msg.text"></p>
                            </div>
                        </div>
                    </template>
                </div>

                @auth
                    <form @submit.prevent="if(draft.trim()) { spaceMsgs.push({ name: '{{ auth()->user()->name }}', text: draft.trim(), mine: true }); draft = ''; }" class="flex items-center gap-2">
                        <input type="text" x-model="draft" placeholder="Kirim pesan ke ruang ini..." class="ny-input text-xs flex-1 bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--primary)]">
                        <button type="submit" class="btn-primary text-xs py-2 px-3.5 shrink-0 cursor-pointer" :disabled="!draft.trim()">
                            Kirim
                        </button>
                    </form>
                @else
                    <p class="text-center text-xs text-[var(--text-muted)]">
                        <button type="button" @click="window.openAuthModal('login')" class="underline hover:text-[var(--primary)]">Masuk</button> untuk ikut berpartisipasi dalam obrolan ruang.
                    </p>
                @endauth
            </div>
        </div>
    </div>

</div>
@endsection
