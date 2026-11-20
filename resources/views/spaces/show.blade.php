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
             myName: @js(auth()->user()?->name ?? 'Kamu'),
             type: '{{ $space->type }}',
             status: '{{ $space->status }}',
             ended: {{ $space->status === 'ended' ? 'true' : 'false' }},
             signalUrl: '{{ route('spaces.signal', $space) }}',
             syncUrl: '{{ route('spaces.sync', $space) }}',
             startUrl: '{{ route('spaces.start', $space) }}',
             requestSpeakUrl: '{{ route('spaces.request_speak', $space) }}',
             micUrl: '{{ route('spaces.mic', $space) }}',
             pinUrl: '{{ route('spaces.pin', $space) }}',
             roleUrlTemplate: @js(route('spaces.participant.role', ['space' => $space->slug, 'user' => '__USER__'])),
             lastSignalId: {{ (int) ($lastSignalId ?? 0) }},
             initialMsgs: @js($initialMessages ?? []),
             initialParticipants: @js($participantsData ?? []),
             initialPinnedProject: @js($pinnedProjectData ?? null),
             isHost: {{ auth()->check() && (int) auth()->id() === (int) $space->host_id ? 'true' : 'false' }},
             isJoined: {{ auth()->check() && ($participants->contains('id', auth()->id()) || (int) auth()->id() === (int) $space->host_id) ? 'true' : 'false' }}
         })">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--border-color)]">
            <div class="min-w-0 space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <template x-if="status === 'ended'">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] text-[10px] font-semibold uppercase tracking-wider">
                            Selesai
                        </span>
                    </template>
                    <template x-if="status === 'scheduled'">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] text-[10px] font-semibold uppercase tracking-wider">
                            Terjadwal
                        </span>
                    </template>
                    <template x-if="status === 'live'">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[10px] font-semibold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Live</span>
                        </span>
                    </template>
                    <h1 class="font-bold text-base sm:text-lg text-[var(--primary)] truncate">{{ $space->title }}</h1>
                </div>

                @if($space->description)
                    <p class="text-xs text-[var(--text-muted)] leading-relaxed">{{ $space->description }}</p>
                @endif

                <p class="text-xs text-[var(--text-muted)]">
                    {{ $space->type === 'video' ? 'Video Space' : 'Audio Space' }} · Host: <strong class="text-[var(--primary)] font-semibold">{{ $space->host?->name }}</strong>
                    @if($space->scheduled_at)
                        <span x-show="status === 'scheduled'">· Jadwal: {{ $space->scheduled_at->translatedFormat('d M Y, H:i') }}</span>
                    @endif
                    <template x-if="status === 'live'">
                        <span>
                            <span class="mx-1">·</span>
                            <span class="font-mono text-[10.5px]" x-text="syncMode === 'reverb' ? 'Reverb Live' : 'Mode Lokal (Sync)'"></span>
                        </span>
                    </template>
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <template x-if="status === 'ended'">
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                        Space Telah Berakhir
                    </span>
                </template>
                <template x-if="status !== 'ended'">
                    <div class="flex items-center gap-2">
                        @guest
                            <button type="button" @click="window.openAuthModal('login')" class="btn-primary text-xs py-2 px-4 cursor-pointer shadow-xs">
                                Masuk untuk Gabung
                            </button>
                        @else
                            <template x-if="!joined">
                                <button type="button" @click="join()" class="btn-primary text-xs py-2 px-4 cursor-pointer shadow-xs">Gabung sebagai Pendengar</button>
                            </template>
                            <template x-if="joined && myRole !== 'host'">
                                <button type="button" @click="leave()" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Keluar</button>
                            </template>
                            @if(auth()->id() === $space->host_id)
                                <template x-if="status === 'scheduled'">
                                    <button type="button" @click="startSpace()" class="btn-primary text-xs py-2 px-4 cursor-pointer shadow-xs" :disabled="busyAction">
                                        Mulai Space Sekarang
                                    </button>
                                </template>
                                <form method="POST" action="{{ route('spaces.end', $space) }}" onsubmit="return confirm('Akhiri space ini untuk semua peserta?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-rose-400 hover:bg-rose-950/30 px-3 py-2 rounded-xl transition cursor-pointer">
                                        Akhiri Space
                                    </button>
                                </form>
                            @endif
                        @endguest
                    </div>
                </template>
            </div>
        </div>

        <!-- Pinned Project Banner + Moderator Pin Controls -->
        <div class="space-y-2" x-show="pinnedProject || (isModerator && status !== 'ended')" x-cloak>
            <template x-if="pinnedProject">
                <div class="rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] font-mono font-semibold uppercase tracking-wider text-[var(--text-muted)]">Karya Disematkan</p>
                        <a :href="'/project/' + pinnedProject.slug" class="text-xs sm:text-sm font-bold text-[var(--primary)] hover:underline truncate block" x-text="pinnedProject.title"></a>
                        <p class="text-[11px] text-[var(--text-muted)] truncate" x-show="pinnedProject.tagline" x-text="pinnedProject.tagline"></p>
                    </div>
                    <a :href="'/project/' + pinnedProject.slug" class="btn-secondary text-[11px] py-1.5 px-3 shrink-0">Lihat Karya</a>
                </div>
            </template>

            @if(isset($pinnableProjects) && $pinnableProjects->count())
                <div x-show="isModerator && status !== 'ended'" class="flex flex-wrap items-center gap-2 text-xs">
                    <select x-model="pinProjectId" class="ny-input text-xs py-1.5 flex-1 min-w-[200px] bg-[var(--surface-input)]">
                        <option value="">Pilih karya untuk disematkan...</option>
                        @foreach($pinnableProjects as $proj)
                            <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                        @endforeach
                    </select>
                    <button type="button" @click="savePinnedProject()" class="btn-secondary text-xs py-1.5 px-3 cursor-pointer" :disabled="busyAction">
                        <span x-text="pinProjectId ? 'Sematkan Karya' : 'Lepas Sematan'"></span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Scheduled Lobby Notice -->
        <template x-if="status === 'scheduled'">
            <div class="rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] p-4 text-xs text-[var(--text-muted)] flex items-center justify-between gap-3">
                <span>Ruang masih dalam status terjadwal. Panggung audio/video aktif otomatis begitu Host memulai Space.</span>
                <span class="font-mono text-[11px] text-[var(--primary)] shrink-0" x-text="participantsList.length + ' di ruang tunggu'"></span>
            </div>
        </template>

        <!-- Stage Viewport Canvas -->
        <div class="rounded-2xl p-4 sm:p-6 bg-[var(--bg-app)] border border-[var(--border-color)] space-y-6">
            <!-- Video grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" x-show="type === 'video'">
                <!-- Local preview (only active for stage roles) -->
                <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0B0B0D] border border-[#242426]" x-show="isOnStage">
                    <video x-ref="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <span class="absolute bottom-2 left-2 text-[10px] font-semibold text-[#0B0B0D] bg-[#EAE8E1]/90 backdrop-blur-xs px-2 py-0.5 rounded-md">Kamu (<span x-text="roleLabel(myRole)"></span>)</span>
                </div>
                <!-- Remote Peers -->
                <template x-for="p in peers" :key="p.id">
                    <div class="relative aspect-video rounded-xl overflow-hidden bg-[#0B0B0D] border border-[#242426]">
                        <video :id="'peer-' + p.id" autoplay playsinline class="w-full h-full object-cover"></video>
                        <span class="absolute bottom-2 left-2 text-[10px] font-semibold text-[#0B0B0D] bg-[#EAE8E1]/90 backdrop-blur-xs px-2 py-0.5 rounded-md" x-text="p.name"></span>
                    </div>
                </template>
            </div>

            <!-- Hidden audio elements for connected WebRTC peers in audio mode -->
            <template x-for="p in peers" :key="'peer-audio-wrap-' + p.id">
                <audio x-show="type === 'audio'" :id="'peer-audio-' + p.id" autoplay playsinline class="hidden"></audio>
            </template>

            <!-- STAGE: Host, Co-hosts & Speakers -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)]">
                        Panggung (<span x-text="stageParticipants.length"></span>)
                    </h2>
                    <span class="text-[11px] text-[var(--text-muted)] font-mono">Maks. {{ \App\Models\Space::MAX_SPEAKERS }} speaker · {{ \App\Models\Space::MAX_COHOSTS }} co-host</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="part in stageParticipants" :key="'stage-' + part.id">
                        <div class="relative flex flex-col items-center gap-2.5 p-3.5 rounded-2xl bg-[var(--surface-subtle)] border text-center shadow-xs transition"
                             :class="!part.is_muted ? 'border-emerald-500/50' : 'border-[var(--border-color)]'">
                            <!-- Floating Quick Reaction -->
                            <template x-if="reactions[part.id]">
                                <span class="absolute -top-2.5 right-2 px-2 py-0.5 rounded-full bg-[var(--primary)] text-[var(--primary-text)] text-[10px] font-bold shadow-xs" x-text="reactions[part.id].label"></span>
                            </template>

                            <div class="relative">
                                <template x-if="part.avatar">
                                    <img :src="part.avatar" :alt="part.name" referrerpolicy="no-referrer" class="w-14 h-14 rounded-2xl object-cover" :class="part.role === 'host' ? 'ring-2 ring-[var(--primary)]' : ''">
                                </template>
                                <template x-if="!part.avatar">
                                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-lg"
                                         :class="part.role === 'host' ? 'bg-[var(--primary)] text-[var(--primary-text)] ring-2 ring-[var(--primary)]' : 'bg-[var(--surface-muted-flat)] text-[var(--primary)] border border-[var(--border-color)]'"
                                         x-text="part.name ? part.name.trim().charAt(0).toUpperCase() : '?'"></div>
                                </template>

                                <!-- Mic indicator dot -->
                                <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border border-[var(--border-color)] flex items-center justify-center"
                                      :class="part.is_muted ? 'bg-[var(--surface)] text-[var(--text-muted)]' : 'bg-emerald-500 text-[#0B0B0D]'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                                </span>
                            </div>

                            <div class="min-w-0 max-w-full space-y-1">
                                <p class="text-xs font-semibold text-[var(--primary)] truncate" x-text="part.name"></p>
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                          :class="part.role === 'host' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]'"
                                          x-text="roleLabel(part.role)"></span>
                                    <span class="text-[10px] font-mono text-rose-400" x-show="part.is_forced_muted">Dibisukan</span>
                                </div>
                            </div>

                            <!-- Host / Co-host moderation buttons -->
                            <div class="flex flex-wrap items-center justify-center gap-1 pt-1" x-show="isModerator && part.role !== 'host' && Number(part.id) !== Number(me) && status !== 'ended'">
                                <template x-if="!(part.role === 'cohost' && myRole !== 'host')">
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        <button type="button"
                                                @click="moderate(part.id, part.is_forced_muted ? 'unmute' : 'mute')"
                                                class="text-[10px] font-semibold px-2 py-1 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-[var(--primary)] cursor-pointer"
                                                x-text="part.is_forced_muted ? 'Izinkan Mic' : 'Mute'"></button>
                                        <button type="button"
                                                @click="moderate(part.id, 'demote_listener')"
                                                class="text-[10px] font-semibold px-2 py-1 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer">
                                            Turunkan
                                        </button>
                                        <template x-if="myRole === 'host'">
                                            <button type="button"
                                                    @click="moderate(part.id, part.role === 'cohost' ? 'demote_cohost' : 'promote_cohost')"
                                                    class="text-[10px] font-semibold px-2 py-1 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-[var(--primary)] cursor-pointer"
                                                    x-text="part.role === 'cohost' ? 'Lepas Co-host' : '+ Co-host'"></button>
                                        </template>
                                        <button type="button"
                                                @click="moderate(part.id, 'kick')"
                                                class="text-[10px] font-semibold px-2 py-1 rounded-lg border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 cursor-pointer">
                                            Keluarkan
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- RAISED HANDS QUEUE (Visible to Host & Co-host) -->
            <div class="space-y-2.5 pt-4 border-t border-[var(--border-color)]" x-show="isModerator && raisedHandParticipants.length > 0 && status === 'live'" x-cloak>
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-amber-400">
                        Permintaan Bicara (<span x-text="raisedHandParticipants.length"></span>)
                    </h2>
                </div>
                <div class="space-y-2">
                    <template x-for="req in raisedHandParticipants" :key="'req-' + req.id">
                        <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-[var(--primary)] truncate" x-text="req.name"></p>
                                <p class="text-[11px] text-[var(--text-muted)]" x-text="'@' + req.username + ' ingin naik ke panggung'"></p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="moderate(req.id, 'approve_speaker')" class="btn-primary text-[11px] py-1.5 px-3 cursor-pointer">Izinkan Naik</button>
                                <button type="button" @click="moderate(req.id, 'decline_request')" class="btn-secondary text-[11px] py-1.5 px-3 cursor-pointer">Tolak</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- LISTENERS: Audience below the stage -->
            <div class="space-y-3 pt-4 border-t border-[var(--border-color)]">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)]">
                        Pendengar (<span x-text="listenerParticipants.length"></span>)
                    </h2>
                    <span class="text-[11px] text-[var(--text-muted)]" x-show="status === 'live'">Mode dengar — perlu izin Host/Co-host untuk bicara</span>
                </div>

                <template x-if="listenerParticipants.length === 0">
                    <p class="text-xs text-[var(--text-muted)] py-2">
                        Belum ada pendengar. Bagikan tautan ruang ini agar developer lain ikut mendengarkan.
                    </p>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5" x-show="listenerParticipants.length > 0">
                    <template x-for="part in listenerParticipants" :key="'listener-' + part.id">
                        <div class="relative flex flex-col gap-2 p-3 rounded-xl bg-[var(--surface-subtle)] border text-left"
                             :class="part.hand_raised ? 'border-amber-500/50' : 'border-[var(--border-color)]'">
                            <template x-if="reactions[part.id]">
                                <span class="absolute -top-2 right-2 px-2 py-0.5 rounded-full bg-[var(--primary)] text-[var(--primary-text)] text-[10px] font-bold shadow-xs" x-text="reactions[part.id].label"></span>
                            </template>

                            <div class="flex items-center gap-2.5 min-w-0">
                                <template x-if="part.avatar">
                                    <img :src="part.avatar" :alt="part.name" referrerpolicy="no-referrer" class="w-8 h-8 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="!part.avatar">
                                    <div class="w-8 h-8 rounded-full bg-[var(--surface-muted-flat)] text-[var(--primary)] flex items-center justify-center font-bold text-xs shrink-0"
                                         x-text="part.name ? part.name.trim().charAt(0).toUpperCase() : '?'"></div>
                                </template>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-[var(--primary)] truncate" x-text="part.name"></p>
                                    <p class="text-[10px] text-amber-400 font-semibold" x-show="part.hand_raised">Angkat tangan</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-[var(--border-color)]/60" x-show="isModerator && status !== 'ended'">
                                <button type="button"
                                        x-show="status === 'live'"
                                        @click="moderate(part.id, 'promote_speaker')"
                                        class="text-[10px] font-semibold px-2.5 py-1 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-[var(--primary)] cursor-pointer"
                                        title="Undang naik ke panggung">
                                    Naikkan
                                </button>
                                <button type="button"
                                        @click="moderate(part.id, 'kick')"
                                        class="text-[10px] font-semibold px-2 py-1 rounded-lg text-rose-400 hover:bg-rose-950/30 cursor-pointer"
                                        title="Keluarkan dari Space">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Role-Based Controls & Quick Reactions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-[var(--border-color)]" x-show="joined && status === 'live'" x-cloak>
            <div class="flex items-center gap-2.5 flex-wrap justify-center">
                <!-- On-stage controls -->
                <template x-if="isOnStage">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleMic()"
                                :disabled="isForcedMuted && !micOn"
                                :class="micOn ? 'bg-[var(--primary)] text-[var(--primary-text)] shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)]'"
                                class="h-10 px-4 rounded-full flex items-center gap-2 text-xs font-semibold transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                            <span x-text="isForcedMuted ? 'Dibisukan Host' : (micOn ? 'Mic Aktif' : 'Mic Mati')"></span>
                        </button>

                        <template x-if="type === 'video'">
                            <button type="button" @click="toggleCam()"
                                    :class="camOn ? 'bg-[var(--primary)] text-[var(--primary-text)] shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)]'"
                                    class="h-10 px-4 rounded-full flex items-center gap-2 text-xs font-semibold transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span x-text="camOn ? 'Kamera Aktif' : 'Kamera Mati'"></span>
                            </button>
                        </template>

                        <template x-if="myRole !== 'host'">
                            <button type="button" @click="moderate(me, 'step_down')" class="btn-secondary text-xs py-2 px-3.5 rounded-full cursor-pointer">
                                Turun ke Pendengar
                            </button>
                        </template>
                    </div>
                </template>

                <!-- Listener controls (mic locked, request to speak) -->
                <template x-if="!isOnStage">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[var(--text-muted)] px-3 py-2 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)]">
                            Mode Pendengar
                        </span>
                        <button type="button" @click="toggleHand()"
                                :disabled="busyAction"
                                :class="isHandRaised ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-[var(--primary)] text-[var(--primary-text)]'"
                                class="h-10 px-4 rounded-full text-xs font-bold transition cursor-pointer">
                            <span x-text="isHandRaised ? 'Turunkan Tangan' : 'Angkat Tangan (Minta Bicara)'"></span>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Quick Reactions -->
            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                <button type="button" @click="sendReaction('agree')" class="px-2.5 py-1.5 rounded-full text-[11px] font-semibold bg-[var(--surface-muted)] hover:bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-color)] cursor-pointer">Setuju</button>
                <button type="button" @click="sendReaction('clap')" class="px-2.5 py-1.5 rounded-full text-[11px] font-semibold bg-[var(--surface-muted)] hover:bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-color)] cursor-pointer">Apresiasi</button>
                <button type="button" @click="sendReaction('bulb')" class="px-2.5 py-1.5 rounded-full text-[11px] font-semibold bg-[var(--surface-muted)] hover:bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-color)] cursor-pointer">Ide Bagus</button>
                <button type="button" @click="sendReaction('question')" class="px-2.5 py-1.5 rounded-full text-[11px] font-semibold bg-[var(--surface-muted)] hover:bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-color)] cursor-pointer">Mau Tanya</button>
            </div>
        </div>

        <!-- Space Chat Pane (Stone & Obsidian Aesthetic) -->
        <div class="pt-2 border-t border-[var(--border-color)]">
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
                                 :class="msg.mine ? 'bg-[var(--primary)] text-[var(--primary-text)] font-medium rounded-br-md' : 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] rounded-bl-md'">
                                <p class="text-[10px] font-bold opacity-75 mb-0.5" x-text="msg.name"></p>
                                <p x-text="msg.text"></p>
                            </div>
                        </div>
                    </template>
                </div>

                @auth
                    <form @submit.prevent="sendChat()" class="flex items-center gap-2" x-show="status !== 'ended'">
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
