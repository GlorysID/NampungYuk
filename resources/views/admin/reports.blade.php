@extends('layouts.app')

@section('title', 'Panel Moderasi — NampungYuk')
@section('header_title', 'Panel Moderasi')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
<div class="space-y-6"
     x-data="{
        warnModalOpen: false,
        warnProjectId: null,
        warnProjectTitle: '',
        warnNotes: '',

        userWarnModalOpen: false,
        userWarnId: null,
        userWarnName: '',
        userWarnNotes: '',

        banModalOpen: false,
        banUserId: null,
        banUserName: '',
        banReason: '',

        deleteUserModalOpen: false,
        deleteUserId: null,
        deleteUserName: '',
        deleteUserUsername: '',
        deleteUserConfirm: '',

        deleteContentModalOpen: false,
        deleteContentUrl: '',
        deleteContentKind: '',
        deleteContentTitle: '',

        openWarn(id, title, currentNotes = '') {
            this.warnProjectId = id;
            this.warnProjectTitle = title;
            this.warnNotes = currentNotes || 'Proyek ini sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.';
            this.warnModalOpen = true;
        },

        openUserWarn(id, name, currentNotes = '') {
            this.userWarnId = id;
            this.userWarnName = name;
            this.userWarnNotes = currentNotes || 'Akun Anda sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.';
            this.userWarnModalOpen = true;
        },

        openBan(id, name) {
            this.banUserId = id;
            this.banUserName = name;
            this.banReason = 'Pelanggaran pedoman komunitas NampungYuk.';
            this.banModalOpen = true;
        },

        openDeleteUser(id, name, username) {
            this.deleteUserId = id;
            this.deleteUserName = name;
            this.deleteUserUsername = username;
            this.deleteUserConfirm = '';
            this.deleteUserModalOpen = true;
        },

        openDeleteContent(url, kind, title) {
            this.deleteContentUrl = url;
            this.deleteContentKind = kind;
            this.deleteContentTitle = title;
            this.deleteContentModalOpen = true;
        }
     }"
     @keydown.escape.window="warnModalOpen = false; userWarnModalOpen = false; banModalOpen = false; deleteUserModalOpen = false; deleteContentModalOpen = false">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--primary)]">Pusat Moderasi</h1>
            <p class="text-[13px] text-[var(--text-muted)]">Tinjau antrean laporan, kelola status pengguna, dan pantau karya yang sedang dalam peringatan.</p>
        </div>
        <span class="inline-flex items-center gap-2 self-start sm:self-auto px-3 py-1.5 rounded-full bg-[var(--surface)] border border-[var(--border-color)] text-xs font-mono text-[var(--text-muted)]">
            <span class="w-2 h-2 rounded-full {{ $pendingCount > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
            <span>{{ $pendingCount > 0 ? $pendingCount . ' laporan perlu ditinjau' : 'Antrean bersih' }}</span>
        </span>
    </div>

    <!-- Flash Notices -->
    @if(session('status'))
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs font-medium">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs font-medium">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 4 Clickable KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'status' => 'pending']) }}"
           class="group rounded-2xl p-4 bg-[var(--surface)] border transition-colors {{ ($section === 'reports' && $reportStatus === 'pending') ? 'border-[var(--primary)]/50 bg-[var(--surface-hover)]/60' : 'border-[var(--border-color)] hover:bg-[var(--surface-hover)]' }}">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Laporan Menunggu</span>
                <span class="w-2 h-2 rounded-full {{ $pendingCount > 0 ? 'bg-rose-500' : 'bg-[var(--text-faint)]' }}"></span>
            </div>
            <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-[var(--primary)]">{{ $pendingCount }}</p>
            <p class="mt-1 text-[11.5px] text-[var(--text-muted)]">{{ $pendingContentReportsCount }} konten &middot; {{ $pendingUserReportsCount }} akun</p>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'flagged']) }}"
           class="group rounded-2xl p-4 bg-[var(--surface)] border transition-colors {{ $section === 'flagged' ? 'border-amber-500/60 bg-[var(--surface-hover)]/60' : 'border-[var(--border-color)] hover:bg-[var(--surface-hover)]' }}">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Konten Berperingatan</span>
                <span class="w-2 h-2 rounded-full {{ $flaggedProjectsCount > 0 ? 'bg-amber-400' : 'bg-[var(--text-faint)]' }}"></span>
            </div>
            <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-[var(--primary)]">{{ $flaggedProjectsCount }}</p>
            <p class="mt-1 text-[11.5px] text-[var(--text-muted)]">Disembunyikan dari feed publik</p>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'all_users', 'filter' => 'warned']) }}"
           class="group rounded-2xl p-4 bg-[var(--surface)] border transition-colors {{ ($section === 'users' && $userFilter === 'warned') ? 'border-amber-500/60 bg-[var(--surface-hover)]/60' : 'border-[var(--border-color)] hover:bg-[var(--surface-hover)]' }}">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Akun Peringatan</span>
                <span class="w-2 h-2 rounded-full {{ $warnedUsersCount > 0 ? 'bg-amber-400' : 'bg-[var(--text-faint)]' }}"></span>
            </div>
            <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-[var(--primary)]">{{ $warnedUsersCount }}</p>
            <p class="mt-1 text-[11.5px] text-[var(--text-muted)]">Banner &amp; notifikasi aktif</p>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'all_users', 'filter' => 'banned']) }}"
           class="group rounded-2xl p-4 bg-[var(--surface)] border transition-colors {{ ($section === 'users' && $userFilter === 'banned') ? 'border-rose-500/60 bg-[var(--surface-hover)]/60' : 'border-[var(--border-color)] hover:bg-[var(--surface-hover)]' }}">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Akun Diblokir</span>
                <span class="w-2 h-2 rounded-full {{ $bannedUsersCount > 0 ? 'bg-rose-500' : 'bg-[var(--text-faint)]' }}"></span>
            </div>
            <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-[var(--primary)]">{{ $bannedUsersCount }}</p>
            <p class="mt-1 text-[11.5px] text-[var(--text-muted)]">Akses akun dibekukan</p>
        </a>
    </div>

    <!-- 3 Primary Section Tabs -->
    <nav aria-label="Navigasi bagian moderasi" class="flex flex-wrap items-center gap-1.5 p-1.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
        <a href="{{ route('admin.reports.index', ['tab' => 'reports']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-[13px] font-semibold transition-colors {{ $section === 'reports' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]' }}">
            <span>Antrean Laporan</span>
            @if($pendingCount > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10.5px] font-mono tabular-nums {{ $section === 'reports' ? 'bg-rose-600 text-white' : 'bg-rose-500/15 text-rose-400' }}">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-[13px] font-semibold transition-colors {{ $section === 'users' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]' }}">
            <span>Manajemen Pengguna</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10.5px] font-mono tabular-nums {{ $section === 'users' ? 'bg-[var(--primary-text)]/15 text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]' }}">
                {{ $allUsersCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'flagged']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-[13px] font-semibold transition-colors {{ $section === 'flagged' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]' }}">
            <span>Konten Berperingatan</span>
            @if($flaggedProjectsCount > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10.5px] font-mono tabular-nums {{ $section === 'flagged' ? 'bg-amber-600 text-white' : 'bg-amber-500/15 text-amber-400' }}">
                    {{ $flaggedProjectsCount }}
                </span>
            @endif
        </a>
    </nav>

    <!-- SECTION 1: ANTREAN LAPORAN -->
    @if($section === 'reports')
        <div class="space-y-4">
            <!-- Sub-filters (Type + Status) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-faint)] mr-1.5">Target:</span>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => 'all', 'status' => $reportStatus]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportType === 'all' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => 'content', 'status' => $reportStatus]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportType === 'content' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        <span>Konten</span>
                        @if($pendingContentReportsCount > 0)
                            <span class="text-[10.5px] font-mono text-amber-400">({{ $pendingContentReportsCount }})</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => 'user', 'status' => $reportStatus]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportType === 'user' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        <span>Akun</span>
                        @if($pendingUserReportsCount > 0)
                            <span class="text-[10.5px] font-mono text-amber-400">({{ $pendingUserReportsCount }})</span>
                        @endif
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-faint)] mr-1.5">Status:</span>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => $reportType, 'status' => 'pending']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportStatus === 'pending' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        Menunggu ({{ $pendingCount }})
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => $reportType, 'status' => 'history']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportStatus === 'history' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        Riwayat ({{ $historyReportsCount }})
                    </a>
                    <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => $reportType, 'status' => 'all']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $reportStatus === 'all' ? 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent' }}">
                        Semua
                    </a>
                </div>
            </div>

            <!-- Reports List -->
            <div class="space-y-3">
                @forelse($reports as $report)
                    @php
                        $target = $report->reportable;
                        $isProject = $report->reportable_type === \App\Models\Project::class;
                        $isPost = $report->reportable_type === \App\Models\CommunityPost::class;
                        $isUser = $report->reportable_type === \App\Models\User::class;

                        $kindLabel = $isProject ? 'Karya' : ($isPost ? 'Postingan Komunitas' : ($isUser ? 'Akun' : 'Entitas'));
                        $statusBadge = match ($report->status) {
                            'resolved' => ['label' => 'Selesai', 'classes' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
                            'dismissed' => ['label' => 'Diabaikan', 'classes' => 'bg-[var(--surface-muted)] text-[var(--text-muted)] border-[var(--border-color)]'],
                            default => ['label' => 'Menunggu', 'classes' => 'bg-amber-500/15 text-amber-300 border-amber-500/30'],
                        };
                    @endphp

                    <article class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                        <!-- Card Top Strip -->
                        <div class="px-5 py-3 bg-[var(--surface-muted)] border-b border-[var(--border-color)] flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]">
                                    {{ $kindLabel }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/25">
                                    {{ $report->reason_label }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusBadge['classes'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>

                            <span class="text-[11px] font-mono text-[var(--text-muted)]">
                                #{{ $report->id }} &middot; {{ $report->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Card Main Body -->
                        <div class="p-5 grid grid-cols-1 lg:grid-cols-12 gap-5">
                            <!-- Target Summary -->
                            <div class="lg:col-span-7 space-y-2.5 min-w-0">
                                <p class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Target Dilaporkan</p>

                                @if($isProject && $target)
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('projects.show', $target->slug) }}" class="text-base font-bold text-[var(--primary)] hover:underline break-words">
                                                {{ $target->title }}
                                            </a>
                                            @if($target->category)
                                                <span class="px-2 py-0.5 rounded-md text-[10.5px] font-mono bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                                                    {{ $target->category->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-xs text-[var(--text-muted)]">
                                            Pembuat:
                                            @if($target->user)
                                                <a href="{{ route('profile.show', $target->user->username) }}" class="text-[var(--primary)] hover:underline font-medium">
                                                    {{ $target->user->name }} ({{ '@' . $target->user->username }})
                                                </a>
                                            @else
                                                <span>Pengguna terhapus</span>
                                            @endif
                                        </p>

                                        @if($target->tagline)
                                            <p class="text-xs text-[var(--text-muted)] line-clamp-2">{{ $target->tagline }}</p>
                                        @endif

                                        @if($target->is_flagged || $target->warning_notes)
                                            <div class="mt-2 px-3 py-2 rounded-xl bg-amber-500/10 border border-amber-500/25 text-xs text-amber-300">
                                                <span class="font-semibold">Status Karya Berperingatan:</span>
                                                <span>{{ $target->warning_notes ?? 'Ditandai oleh moderator.' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($isPost && $target)
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--text-muted)]">
                                            @if($target->community)
                                                <a href="{{ route('communities.show', $target->community->slug) }}" class="font-semibold text-[var(--primary)] hover:underline">
                                                    {{ $target->community->name }}
                                                </a>
                                                <span>&middot;</span>
                                            @endif
                                            @if($target->user)
                                                <a href="{{ route('profile.show', $target->user->username) }}" class="hover:text-[var(--primary)] transition">
                                                    {{ '@' . $target->user->username }}
                                                </a>
                                            @else
                                                <span>Pengguna terhapus</span>
                                            @endif
                                        </div>

                                        <blockquote class="px-3.5 py-2.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] text-[13px] text-[var(--primary)] leading-relaxed break-words">
                                            {{ \Illuminate\Support\Str::limit($target->content, 360) }}
                                        </blockquote>
                                    </div>
                                @elseif($isUser && $target)
                                    <div class="flex items-start gap-3">
                                        <x-user-avatar :user="$target" size="md" />
                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('profile.show', $target->username) }}" class="text-sm font-bold text-[var(--primary)] hover:underline">
                                                    {{ $target->name }}
                                                </a>
                                                <span class="text-xs font-mono text-[var(--text-muted)]">{{ '@' . $target->username }}</span>
                                                @if($target->isBanned())
                                                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/30">Diblokir</span>
                                                @elseif($target->hasWarning())
                                                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">Peringatan Aktif</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">Aktif</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-[var(--text-muted)] truncate">{{ $target->email }}</p>

                                            @if($target->hasWarning())
                                                <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/25 rounded-lg px-2.5 py-1.5 mt-1.5">
                                                    Catatan Peringatan: {{ $target->warning_notes }}
                                                </p>
                                            @endif
                                            @if($target->isBanned() && $target->ban_reason)
                                                <p class="text-xs text-rose-300 bg-rose-500/10 border border-rose-500/25 rounded-lg px-2.5 py-1.5 mt-1.5">
                                                    Alasan Blokir: {{ $target->ban_reason }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <p class="text-xs italic text-[var(--text-muted)] px-3 py-2 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)]">
                                        Target laporan ini sudah dihapus dari sistem.
                                    </p>
                                @endif
                            </div>

                            <!-- Reporter & Resolution Details -->
                            <div class="lg:col-span-5 lg:border-l lg:border-[var(--border-color)] lg:pl-5 space-y-2.5 min-w-0">
                                <p class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Detail Pelapor</p>
                                <p class="text-xs text-[var(--text-muted)]">
                                    Dilaporkan oleh:
                                    @if($report->reporter)
                                        <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-semibold text-[var(--primary)] hover:underline">
                                            {{ $report->reporter->name }} ({{ '@' . $report->reporter->username }})
                                        </a>
                                    @else
                                        <span>Pengguna terhapus</span>
                                    @endif
                                </p>

                                @if($report->notes)
                                    <p class="text-xs text-[var(--text-muted)] px-3 py-2 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] leading-relaxed break-words">
                                        "{{ $report->notes }}"
                                    </p>
                                @else
                                    <p class="text-xs italic text-[var(--text-faint)]">Tidak ada keterangan tambahan dari pelapor.</p>
                                @endif

                                @if($report->status !== 'pending')
                                    <div class="pt-2 border-t border-[var(--border-color)] text-xs text-[var(--text-muted)] space-y-1">
                                        <p>
                                            Ditangani oleh <span class="font-semibold text-[var(--primary)]">{{ $report->resolver?->name ?? 'Admin' }}</span>
                                            @if($report->resolved_at)
                                                &middot; <span class="font-mono">{{ $report->resolved_at->diffForHumans() }}</span>
                                            @endif
                                        </p>
                                        @if($report->admin_notes)
                                            <p class="text-[var(--text-soft)]">{{ $report->admin_notes }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="px-5 py-3 bg-[var(--surface-muted)]/60 border-t border-[var(--border-color)] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <!-- Target Escalation Actions -->
                            <div class="flex flex-wrap items-center gap-2">
                                @if($isProject && $target)
                                    <a href="{{ route('projects.show', $target->slug) }}" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                                        Lihat Karya
                                    </a>

                                    <button type="button"
                                            @click="openWarn({{ $target->id }}, @js($target->title), @js($target->warning_notes ?? ''))"
                                            class="px-3 py-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition cursor-pointer">
                                        {{ $target->warning_notes ? 'Edit Peringatan' : 'Beri Peringatan' }}
                                    </button>

                                    @if($target->is_flagged || $target->warning_notes)
                                        <form method="POST" action="{{ route('admin.projects.warn', $target->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                                                Cabut Peringatan
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button"
                                            @click="openDeleteContent(@js(route('admin.projects.destroy', $target->id)), 'Karya', @js($target->title))"
                                            class="px-3 py-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                                        Hapus Proyek
                                    </button>
                                @elseif($isPost && $target)
                                    @if($target->community)
                                        <a href="{{ route('communities.show', $target->community->slug) }}" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                                            Lihat Diskusi
                                        </a>
                                    @endif

                                    <button type="button"
                                            @click="openDeleteContent(@js(route('admin.community-posts.destroy', $target->id)), 'Postingan Komunitas', @js(\Illuminate\Support\Str::limit($target->content, 100)))"
                                            class="px-3 py-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                                        Hapus Postingan
                                    </button>
                                @elseif($isUser && $target)
                                    <a href="{{ route('profile.show', $target->username) }}" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                                        Lihat Profil
                                    </a>

                                    @if(! $target->is_admin && $target->id !== auth()->id())
                                        <button type="button"
                                                @click="openUserWarn({{ $target->id }}, @js($target->name . ' (@' . $target->username . ')'), @js($target->warning_notes ?? ''))"
                                                class="px-3 py-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition cursor-pointer">
                                            {{ $target->hasWarning() ? 'Edit Peringatan' : 'Beri Peringatan' }}
                                        </button>

                                        @if($target->hasWarning())
                                            <form method="POST" action="{{ route('admin.users.warn', $target->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                                                    Cabut Peringatan
                                                </button>
                                            </form>
                                        @endif

                                        @if($target->isBanned())
                                            <form method="POST" action="{{ route('admin.users.unban', $target->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/20 transition cursor-pointer">
                                                    Buka Blokir
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                    @click="openBan({{ $target->id }}, @js($target->name . ' (@' . $target->username . ')'))"
                                                    class="px-3 py-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                                                Blokir Akun
                                            </button>
                                        @endif

                                        <button type="button"
                                                @click="openDeleteUser({{ $target->id }}, @js($target->name), @js($target->username))"
                                                class="px-3 py-1.5 rounded-lg border border-rose-500/30 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                                            Hapus Akun
                                        </button>
                                    @endif
                                @endif
                            </div>

                            <!-- Report Queue Resolution Actions -->
                            @if($report->status === 'pending')
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary text-xs py-1.5 px-3.5 cursor-pointer">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary text-xs py-1.5 px-3.5 cursor-pointer">
                                            Abaikan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-3">
                        <p class="text-sm font-semibold text-[var(--primary)]">
                            {{ $reportStatus === 'pending' ? 'Tidak ada laporan menunggu pada filter ini.' : 'Belum ada laporan yang cocok dengan filter.' }}
                        </p>
                        <p class="text-xs text-[var(--text-muted)]">
                            Semua laporan yang masuk dari pengguna akan muncul di antrean ini sesuai kategori dan statusnya.
                        </p>
                        @if($reportStatus === 'pending' && $historyReportsCount > 0)
                            <div>
                                <a href="{{ route('admin.reports.index', ['tab' => 'reports', 'type' => $reportType, 'status' => 'history']) }}"
                                   class="btn-secondary text-xs py-2 px-4 inline-flex items-center">
                                    Lihat Riwayat Laporan ({{ $historyReportsCount }})
                                </a>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($reports->hasPages())
                <div class="pt-2">{{ $reports->links() }}</div>
            @endif
        </div>

    <!-- SECTION 2: MANAJEMEN PENGGUNA -->
    @elseif($section === 'users')
        <div class="space-y-4">
            <!-- Unified Search + Status Chips Toolbar -->
            <div class="p-4 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] space-y-3">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col sm:flex-row gap-2.5">
                    <input type="hidden" name="tab" value="all_users">
                    <input type="hidden" name="filter" value="{{ $userFilter }}">
                    <div class="relative flex-1">
                        <input type="text"
                               name="q"
                               value="{{ $searchQuery }}"
                               placeholder="Cari nama, @username, atau email pengguna..."
                               class="w-full h-10 pl-3.5 pr-3.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs sm:text-[13px] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-[var(--primary)] transition">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-primary h-10 px-4 text-xs cursor-pointer">
                            Cari
                        </button>
                        @if($searchQuery !== '')
                            <a href="{{ route('admin.reports.index', ['tab' => 'all_users', 'filter' => $userFilter]) }}"
                               class="btn-secondary h-10 px-4 text-xs inline-flex items-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-[var(--border-color)]">
                    @php
                        $userFilterTabs = [
                            'all' => ['label' => 'Semua', 'count' => $allUsersCount],
                            'active' => ['label' => 'Aktif', 'count' => $activeUsersCount],
                            'warned' => ['label' => 'Dalam Peringatan', 'count' => $warnedUsersCount],
                            'banned' => ['label' => 'Diblokir', 'count' => $bannedUsersCount],
                            'admin' => ['label' => 'Admin', 'count' => $adminUsersCount],
                        ];
                    @endphp
                    @foreach($userFilterTabs as $fKey => $fMeta)
                        <a href="{{ route('admin.reports.index', array_filter(['tab' => 'all_users', 'filter' => $fKey, 'q' => $searchQuery !== '' ? $searchQuery : null])) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $userFilter === $fKey ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                            <span>{{ $fMeta['label'] }}</span>
                            <span class="font-mono text-[10.5px] opacity-80">({{ $fMeta['count'] }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Structured User Table / List -->
            <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden divide-y divide-[var(--border-color)]">
                <div class="hidden lg:grid lg:grid-cols-12 gap-4 px-5 py-3 bg-[var(--surface-muted)] text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <div class="col-span-4">Pengguna</div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Aktivitas</div>
                    <div class="col-span-4 text-right">Tindakan</div>
                </div>

                @forelse($allUsers as $u)
                    <div class="px-5 py-4 space-y-2.5 hover:bg-[var(--surface-hover)]/40 transition-colors">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-4 items-center">
                            <!-- Identity -->
                            <div class="lg:col-span-4 flex items-center gap-3 min-w-0">
                                <x-user-avatar :user="$u" size="md" />
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('profile.show', $u->username) }}" class="text-sm font-semibold text-[var(--primary)] hover:underline truncate">
                                            {{ $u->name }}
                                        </a>
                                    </div>
                                    <p class="text-xs font-mono text-[var(--text-muted)] truncate">{{ '@' . $u->username }}</p>
                                    <p class="text-[11.5px] text-[var(--text-faint)] truncate">{{ $u->email }}</p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="lg:col-span-2 flex flex-wrap items-center gap-1.5">
                                @if($u->is_admin)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Admin</span>
                                @elseif($u->isBanned())
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/30">Diblokir</span>
                                @elseif($u->hasWarning())
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">Peringatan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">Aktif</span>
                                @endif

                                @if($u->isBanned() && $u->hasWarning())
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">Peringatan</span>
                                @endif
                            </div>

                            <!-- Activity -->
                            <div class="lg:col-span-2 text-xs text-[var(--text-muted)] space-y-0.5">
                                <p class="font-mono text-[var(--primary)]">{{ $u->projects_count }} karya</p>
                                <p class="text-[11px]">Gabung {{ $u->created_at->format('d M Y') }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="lg:col-span-4 flex flex-wrap items-center lg:justify-end gap-1.5">
                                <a href="{{ route('profile.show', $u->username) }}"
                                   class="px-2.5 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                                    Profil
                                </a>

                                @if($u->is_admin)
                                    <span class="px-2.5 py-1.5 text-[11px] font-mono text-[var(--text-faint)]">Akun Administrator</span>
                                @else
                                    <button type="button"
                                            @click="openUserWarn({{ $u->id }}, @js($u->name . ' (@' . $u->username . ')'), @js($u->warning_notes ?? ''))"
                                            class="px-2.5 py-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition cursor-pointer">
                                        {{ $u->hasWarning() ? 'Edit Peringatan' : 'Beri Peringatan' }}
                                    </button>

                                    @if($u->hasWarning())
                                        <form method="POST" action="{{ route('admin.users.warn', $u->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                                                Cabut
                                            </button>
                                        </form>
                                    @endif

                                    @if($u->isBanned())
                                        <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/20 transition cursor-pointer">
                                                Buka Blokir
                                            </button>
                                        </form>
                                    @else
                                        <button type="button"
                                                @click="openBan({{ $u->id }}, @js($u->name . ' (@' . $u->username . ')'))"
                                                class="px-2.5 py-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                                            Blokir
                                        </button>
                                    @endif

                                    <button type="button"
                                            @click="openDeleteUser({{ $u->id }}, @js($u->name), @js($u->username))"
                                            class="px-2.5 py-1.5 rounded-lg border border-rose-500/30 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Active Moderation Notes Sub-strip -->
                        @if($u->hasWarning() || ($u->isBanned() && $u->ban_reason))
                            <div class="flex flex-col gap-1.5 pl-0 lg:pl-13">
                                @if($u->hasWarning())
                                    <div class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/25 text-xs text-amber-300">
                                        <span class="font-semibold">Peringatan{{ $u->warned_at ? ' (' . $u->warned_at->diffForHumans() . ')' : '' }}:</span>
                                        <span>{{ $u->warning_notes }}</span>
                                    </div>
                                @endif
                                @if($u->isBanned() && $u->ban_reason)
                                    <div class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300">
                                        <span class="font-semibold">Alasan Blokir{{ $u->banned_at ? ' (' . $u->banned_at->diffForHumans() . ')' : '' }}:</span>
                                        <span>{{ $u->ban_reason }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-12 text-center space-y-3">
                        <p class="text-sm font-semibold text-[var(--primary)]">Tidak ada pengguna yang cocok dengan filter ini.</p>
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="btn-secondary text-xs py-2 px-4 inline-flex items-center">
                            Tampilkan Semua Pengguna
                        </a>
                    </div>
                @endforelse
            </div>

            @if($allUsers->hasPages())
                <div class="pt-2">{{ $allUsers->links() }}</div>
            @endif
        </div>

    <!-- SECTION 3: KONTEN BERPERINGATAN -->
    @elseif($section === 'flagged')
        <div class="space-y-3">
            @forelse($flaggedProjects as $project)
                <article class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            @if($project->thumbnail)
                                <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-16 h-12 rounded-xl object-cover border border-[var(--border-color)] shrink-0 bg-[var(--surface-muted)]">
                            @endif
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('projects.show', $project->slug) }}" class="text-base font-bold text-[var(--primary)] hover:underline break-words">
                                        {{ $project->title }}
                                    </a>
                                    @if($project->category)
                                        <span class="px-2 py-0.5 rounded-md text-[10.5px] font-mono bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                                            {{ $project->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-[var(--text-muted)]">
                                    Pembuat:
                                    @if($project->user)
                                        <a href="{{ route('profile.show', $project->user->username) }}" class="text-[var(--primary)] hover:underline font-medium">
                                            {{ $project->user->name }} ({{ '@' . $project->user->username }})
                                        </a>
                                    @else
                                        <span>Pengguna terhapus</span>
                                    @endif
                                    &middot;
                                    <span class="font-mono">Diperbarui {{ $project->updated_at->diffForHumans() }}</span>
                                </p>
                            </div>
                        </div>

                        <span class="self-start px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30 shrink-0">
                            Disembunyikan dari Feed
                        </span>
                    </div>

                    <div class="px-3.5 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/25 text-xs text-amber-300">
                        <span class="font-semibold">Catatan Peringatan:</span>
                        <span>{{ $project->warning_notes ?? 'Ditandai oleh administrator tanpa catatan khusus.' }}</span>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-[var(--border-color)]">
                        <a href="{{ route('projects.show', $project->slug) }}" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                            Lihat Karya
                        </a>

                        <button type="button"
                                @click="openWarn({{ $project->id }}, @js($project->title), @js($project->warning_notes ?? ''))"
                                class="px-3 py-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition cursor-pointer">
                            Ubah Peringatan
                        </button>

                        <form method="POST" action="{{ route('admin.projects.warn', $project->id) }}">
                            @csrf
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="px-3 py-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/20 transition cursor-pointer">
                                Cabut Peringatan
                            </button>
                        </form>

                        <button type="button"
                                @click="openDeleteContent(@js(route('admin.projects.destroy', $project->id)), 'Karya', @js($project->title))"
                                class="px-3 py-1.5 rounded-lg border border-rose-500/30 bg-rose-500/10 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                            Hapus Proyek
                        </button>
                    </div>
                </article>
            @empty
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-2">
                    <p class="text-sm font-semibold text-[var(--primary)]">Tidak ada konten berperingatan saat ini.</p>
                    <p class="text-xs text-[var(--text-muted)]">Karya yang diberi peringatan moderasi akan tampil di daftar ini hingga dicabut atau dihapus.</p>
                </div>
            @endforelse

            @if($flaggedProjects->hasPages())
                <div class="pt-2">{{ $flaggedProjects->links() }}</div>
            @endif
        </div>
    @endif

    <!-- MODAL BERI PERINGATAN PROYEK -->
    <div x-show="warnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="warnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-amber-400">Pasang Peringatan Resmi pada Karya</h3>
                    <p class="text-xs text-[var(--text-muted)]">Karya: <strong class="text-[var(--primary)]" x-text="warnProjectTitle"></strong></p>
                </div>

                <form method="POST" :action="'/admin/project/' + warnProjectId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--primary)]">Catatan Peringatan (Maks. 500 karakter):</label>
                        <textarea name="warning_notes" x-model="warnNotes" rows="3" maxlength="500" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none focus:border-amber-500 transition resize-none"></textarea>
                        <p class="text-[11px] text-[var(--text-muted)]">Karya akan disembunyikan dari feed publik dan pemilik karya menerima notifikasi peringatan.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="warnModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold text-xs cursor-pointer transition">Pasang Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BERI PERINGATAN PENGGUNA -->
    <div x-show="userWarnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="userWarnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-amber-400">Beri Peringatan ke Akun Pengguna</h3>
                    <p class="text-xs text-[var(--text-muted)]">Pengguna: <strong class="text-[var(--primary)]" x-text="userWarnName"></strong></p>
                </div>

                <form method="POST" :action="'/admin/user/' + userWarnId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--primary)]">Catatan Peringatan Akun (Maks. 500 karakter):</label>
                        <textarea name="warning_notes" x-model="userWarnNotes" rows="3" maxlength="500" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none focus:border-amber-500 transition resize-none"></textarea>
                        <p class="text-[11px] text-[var(--text-muted)]">Pemilik akun akan menerima notifikasi serta melihat banner peringatan di profil dan bar atas saat login.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="userWarnModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold text-xs cursor-pointer transition">Kirim Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BAN AKUN PENGGUNA -->
    <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="banModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-rose-400">Blokir Akun Pengguna (Ban)</h3>
                    <p class="text-xs text-[var(--text-muted)]">Pengguna: <strong class="text-[var(--primary)]" x-text="banUserName"></strong></p>
                </div>

                <form method="POST" :action="'/admin/user/' + banUserId + '/ban'" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--primary)]">Alasan Penonaktifan Akun:</label>
                        <textarea name="ban_reason" x-model="banReason" rows="3" maxlength="500" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none focus:border-rose-500 transition resize-none"></textarea>
                        <p class="text-[11px] text-[var(--text-muted)]">Akun langsung dikeluarkan dari sesi aktif dan alasan ini ditampilkan saat pengguna mencoba masuk kembali.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="banModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs cursor-pointer transition">Blokir Akun Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS AKUN PENGGUNA PERMANEN -->
    <div x-show="deleteUserModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="deleteUserModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div class="min-w-0 space-y-1">
                        <h3 class="text-base font-bold text-rose-400">Hapus Akun Pengguna Permanen</h3>
                        <p class="text-xs text-[var(--text-muted)]">
                            <strong class="text-[var(--primary)]" x-text="deleteUserName"></strong>
                            (<span class="font-mono text-[var(--primary)]" x-text="'@' + deleteUserUsername"></span>)
                        </p>
                    </div>
                </div>

                <div class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300 space-y-1">
                    <p class="font-semibold">Tindakan ini tidak dapat dibatalkan.</p>
                    <p class="text-rose-200/80">Seluruh karya, komentar, postingan komunitas, laporan pending, dan media profil akun ini akan dihapus permanen dan tidak bisa dipulihkan.</p>
                </div>

                <form method="POST" :action="'/admin/user/' + deleteUserId" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[var(--primary)]">
                            Ketik username <code class="px-1.5 py-0.5 rounded bg-[var(--surface-muted)] border border-[var(--border-color)] font-mono text-rose-300" x-text="deleteUserUsername"></code> untuk konfirmasi:
                        </label>
                        <input type="text"
                               x-model="deleteUserConfirm"
                               :placeholder="deleteUserUsername"
                               autocomplete="off"
                               class="w-full h-10 px-3 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs font-mono text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-rose-500 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="deleteUserModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit"
                                :disabled="deleteUserConfirm.trim() !== deleteUserUsername"
                                class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs cursor-pointer transition">
                            Hapus Akun Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS KONTEN PERMANEN -->
    <div x-show="deleteContentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="deleteContentModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </span>
                    <div class="min-w-0 space-y-1">
                        <h3 class="text-base font-bold text-rose-400" x-text="'Hapus ' + deleteContentKind + ' Permanen'"></h3>
                        <p class="text-xs text-[var(--text-muted)] break-words" x-text="deleteContentTitle"></p>
                    </div>
                </div>

                <p class="text-xs text-[var(--text-muted)] px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-200/80">
                    Tindakan ini tidak dapat dibatalkan dan laporan terkait otomatis ditandai selesai.
                </p>

                <form method="POST" :action="deleteContentUrl" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="deleteContentModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs cursor-pointer transition">Hapus Permanen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
