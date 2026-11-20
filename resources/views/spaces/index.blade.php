@extends('layouts.app')

@section('title', 'Spaces — NampungYuk')
@section('header_title', 'Spaces')

@section('content')
@php
    $eligibility = $hostEligibility ?? ['eligible' => false, 'is_admin' => false, 'checks' => []];
    $canCreateSpace = (bool) ($eligibility['eligible'] ?? false);
    $checks = $eligibility['checks'] ?? [];
    $followersCheck = $checks['followers'] ?? ['current' => 0, 'required' => \App\Models\Space::MIN_FOLLOWERS_TO_CREATE, 'passed' => false];
    $projectsCheck = $checks['projects'] ?? ['current' => 0, 'required' => \App\Models\Space::MIN_PROJECTS_TO_CREATE, 'passed' => false];
    $reputationCheck = $checks['reputation'] ?? ['current' => 0, 'required' => \App\Models\Space::MIN_REPUTATION_TO_CREATE, 'passed' => false];
    $emailVerifiedPassed = (bool) ($checks['email_verified']['passed'] ?? false);
    $portfolioPassed = (bool) ($checks['portfolio']['passed'] ?? false);
    $cleanStandingPassed = (bool) ($checks['clean_standing']['passed'] ?? false);
@endphp

<div class="space-y-6 max-w-4xl mx-auto" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, startMode: '{{ old('start_mode', 'now') }}' }">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] text-xs font-semibold mb-1.5">
                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                <span>Ruang Audio &amp; Video Terstruktur</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--primary)] tracking-tight">Spaces</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Diskusi langsung dengan panggung terstruktur: Host, Co-host, Speaker, dan Pendengar.</p>
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
                    @if($s->description)
                        <p class="text-xs text-[var(--text-muted)] line-clamp-1">{{ $s->description }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--text-muted)]">
                        <span class="inline-flex items-center gap-1 font-medium">
                            @if($s->type === 'video')
                                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Video</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                                <span>Audio</span>
                            @endif
                        </span>
                        <span>·</span>
                        <span>Host: {{ $s->host?->name }}</span>
                        <span>·</span>
                        <span class="font-mono">{{ number_format($s->participants_count ?? 0) }} peserta</span>
                        @if($s->pinnedProject)
                            <span>·</span>
                            <span class="text-[var(--primary)] font-medium truncate max-w-[200px]">Topik Karya: {{ $s->pinnedProject->title }}</span>
                        @endif
                    </div>
                </div>
                <span class="btn-primary text-sm py-2 px-4 shrink-0">Gabung</span>
            </a>
        @empty
            <x-empty-state title="Belum ada Space live" description="Space dapat dimulai oleh kreator terverifikasi dan developer bereputasi tinggi." />
        @endforelse
    </div>

    <!-- Scheduled Spaces -->
    @if(isset($scheduled) && $scheduled->count())
        <div class="space-y-3 pt-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Dijadwalkan</h2>
            <div class="space-y-2.5">
                @foreach($scheduled as $s)
                    <a href="{{ route('spaces.show', $s->slug) }}" class="bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-4 flex items-center justify-between gap-4 shadow-xs transition-colors group">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                                    Terjadwal
                                </span>
                                <p class="font-bold text-sm text-[var(--primary)] group-hover:text-[var(--primary-hover)] transition truncate">{{ $s->title }}</p>
                            </div>
                            <p class="text-xs text-[var(--text-muted)]">
                                {{ $s->scheduled_at ? $s->scheduled_at->translatedFormat('d M Y, H:i') : 'Segera' }}
                                · Host: {{ $s->host?->name }}
                                · {{ $s->type === 'video' ? 'Video' : 'Audio' }}
                            </p>
                        </div>
                        <span class="btn-secondary text-xs py-2 px-3.5 shrink-0">Ruang Tunggu</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent / Ended Spaces -->
    @if($recent->count())
        <div class="space-y-3 pt-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Selesai</h2>
            <div class="space-y-2">
                @foreach($recent as $s)
                    <a href="{{ route('spaces.show', $s->slug) }}" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-3.5 flex items-center justify-between gap-3 shadow-xs opacity-75 hover:opacity-100 transition">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-xs text-[var(--primary)] truncate">{{ $s->title }}</p>
                            <p class="text-[10px] text-[var(--text-muted)] mt-0.5">{{ $s->ended_at?->diffForHumans() }} · Host: {{ $s->host?->name }}</p>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0">Selesai</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Create / Eligibility Gate Modal -->
    @auth
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" @click.self="createOpen = false">
            @if(! $canCreateSpace)
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 space-y-4 w-full max-w-md shadow-xl text-left">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-[var(--primary)]">Syarat Host Space</h3>
                        <button type="button" @click="createOpen = false" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] p-4 space-y-3">
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                            Untuk menjaga kualitas diskusi panggung, fitur membuat Space hanya terbuka bagi kreator yang sudah dikenal dan memiliki rekam jejak karya profesional:
                        </p>

                        <ul class="space-y-2 text-xs">
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Email terverifikasi</span>
                                <span class="font-mono text-[11px] {{ $emailVerifiedPassed ? 'text-emerald-400' : 'text-[var(--text-muted)]' }}">
                                    {{ $emailVerifiedPassed ? 'Terpenuhi' : 'Belum' }}
                                </span>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Pengikut akun</span>
                                <span class="font-mono text-[11px] {{ $followersCheck['passed'] ? 'text-emerald-400' : 'text-[var(--text-muted)]' }}">
                                    {{ number_format($followersCheck['current']) }} / {{ number_format($followersCheck['required']) }}
                                </span>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Karya publik aktif</span>
                                <span class="font-mono text-[11px] {{ $projectsCheck['passed'] ? 'text-emerald-400' : 'text-[var(--text-muted)]' }}">
                                    {{ number_format($projectsCheck['current']) }} / {{ number_format($projectsCheck['required']) }}
                                </span>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Poin reputasi</span>
                                <span class="font-mono text-[11px] {{ $reputationCheck['passed'] ? 'text-emerald-400' : 'text-[var(--text-muted)]' }}">
                                    {{ number_format($reputationCheck['current']) }} / {{ number_format($reputationCheck['required']) }}
                                </span>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Tautan GitHub atau Website di profil</span>
                                <span class="font-mono text-[11px] {{ $portfolioPassed ? 'text-emerald-400' : 'text-[var(--text-muted)]' }}">
                                    {{ $portfolioPassed ? 'Terpenuhi' : 'Belum diisi' }}
                                </span>
                            </li>
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-[var(--primary)]">Akun bebas peringatan moderasi</span>
                                <span class="font-mono text-[11px] {{ $cleanStandingPassed ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $cleanStandingPassed ? 'Bersih' : 'Ada Catatan' }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1">
                        <a href="{{ route('settings.index') }}" class="btn-secondary text-xs py-2 px-3.5">Lengkapi Profil</a>
                        <button type="button" @click="createOpen = false" class="btn-primary text-xs py-2 px-4 cursor-pointer">Mengerti</button>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('spaces.store') }}" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 space-y-4 w-full max-w-md shadow-xl text-left">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-[var(--primary)]">Mulai Space Baru</h3>
                        <button type="button" @click="createOpen = false" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if($eligibility['is_admin'] ?? false)
                        <p class="text-[11px] font-mono text-[var(--text-muted)] bg-[var(--surface-subtle)] border border-[var(--border-color)] rounded-xl px-3 py-2">
                            Akses Admin aktif — bebas verifikasi ambang kreator.
                        </p>
                    @endif

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-[var(--primary)]">Judul Space</label>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="80" class="ny-input text-sm" placeholder="mis. Bedah arsitektur backend &amp; database">
                        @error('title') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-[var(--primary)]">Agenda / Topik Singkat (Opsional)</label>
                        <textarea name="description" rows="2" maxlength="300" class="ny-input text-sm resize-none" placeholder="Bahasan utama, aturan panggung, atau target diskusi...">{{ old('description') }}</textarea>
                        @error('description') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-[var(--primary)]">Tipe Ruang</label>
                            <select name="type" class="ny-input text-sm">
                                <option value="audio" @selected(old('type', 'audio') === 'audio')>Audio Saja</option>
                                <option value="video" @selected(old('type') === 'video')>Audio &amp; Video</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-[var(--primary)]">Waktu Mulai</label>
                            <select name="start_mode" x-model="startMode" class="ny-input text-sm">
                                <option value="now">Mulai Sekarang</option>
                                <option value="scheduled">Jadwalkan Nanti</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1" x-show="startMode === 'scheduled'" x-cloak>
                        <label class="text-xs font-semibold text-[var(--primary)]">Jadwal Mulai</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="ny-input text-sm">
                        @error('scheduled_at') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    @if($pinnableProjects->count())
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-[var(--primary)]">Sematkan Karya Bahasan (Opsional)</label>
                            <select name="pinned_project_id" class="ny-input text-sm">
                                <option value="">Tanpa karya disematkan</option>
                                @foreach($pinnableProjects as $proj)
                                    <option value="{{ $proj->id }}" @selected((string) old('pinned_project_id') === (string) $proj->id)>{{ $proj->title }}</option>
                                @endforeach
                            </select>
                            @error('pinned_project_id') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-4 cursor-pointer" x-text="startMode === 'scheduled' ? 'Jadwalkan Space' : 'Mulai Space'"></button>
                    </div>
                </form>
            @endif
        </div>
    @endauth

</div>
@endsection
