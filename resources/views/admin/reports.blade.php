@extends('layouts.app')

@section('title', 'Panel Moderasi — NampungYuk')
@section('header_title', 'Moderasi Laporan')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
<div class="space-y-6" x-data="{
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

    openWarn(id, title) {
        this.warnProjectId = id;
        this.warnProjectTitle = title;
        this.warnNotes = 'Proyek ini sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.';
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
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--border-color)]">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold uppercase tracking-wider mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Administrator Space</span>
            </div>
            <h1 class="text-2xl font-black text-[var(--primary)] tracking-tight">Panel Moderasi & Pengguna</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1">Kelola pengguna secara langsung, tinjau laporan konten, beri peringatan, atau blokir akun yang melanggar.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400 font-bold">
                {{ $pendingCount }} Laporan Menunggu
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-[var(--border-color)] pb-px overflow-x-auto text-xs font-semibold">
        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'all_users' ? 'border-[var(--primary)] text-[var(--primary)] bg-[var(--surface-hover)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Kelola Semua Pengguna</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                {{ $allUsersCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'projects']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'projects' ? 'border-[var(--primary)] text-[var(--primary)] bg-[var(--surface-hover)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            <span>Laporan Konten</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($pendingProjectReportsCount + ($pendingCommunityPostReportsCount ?? 0)) > 0 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]' }}">
                {{ $pendingProjectReportsCount + ($pendingCommunityPostReportsCount ?? 0) }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'users']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'users' ? 'border-[var(--primary)] text-[var(--primary)] bg-[var(--surface-hover)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            <span>Laporan Akun</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $pendingUserReportsCount > 0 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]' }}">
                {{ $pendingUserReportsCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'banned']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'banned' ? 'border-[var(--primary)] text-[var(--primary)] bg-[var(--surface-hover)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            <span>Akun Diblokir</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                {{ $bannedUsersCount }}
            </span>
        </a>
    </div>

    <!-- TAB 1: LAPORAN KONTEN (PROYEK / COMMUNITY POST / COMMENT) -->
    @if($tab === 'projects')
        <div class="space-y-3">
            @forelse($projectReports as $report)
                @php $reportable = $report->reportable; @endphp
                <div class="bg-[var(--surface)] p-5 space-y-4 rounded-2xl border shadow-xs transition {{ $report->status ==='pending' ?'border-[var(--border-strong)] bg-[var(--surface-input)]' :'border-[var(--border-color)]' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            @if($reportable instanceof \App\Models\Project && $reportable->thumbnail)
                                <img src="{{ $reportable->thumbnail }}" alt="{{ $reportable->title }}" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-[var(--border-color)]">
                            @elseif($reportable instanceof \App\Models\CommunityPost)
                                <div class="w-14 h-14 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-xs text-[var(--text-muted)] shrink-0 font-mono">
                                    [Post] </div>
                            @elseif($reportable instanceof \App\Models\ProjectComment)
                                <div class="w-14 h-14 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-xs text-[var(--text-muted)] shrink-0 font-mono">
                                    [Komentar] </div>
                            @else
                                <div class="w-14 h-14 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-xs text-[var(--text-muted)] shrink-0 font-mono">
                                    [App] </div>
                            @endif

                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $report->status === 'pending' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : ($report->status === 'resolved' ? 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]') }}">
                                        {{ $report->status }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                                        {{ $report->reason_label }}
                                    </span>
                                    <span class="text-[11px] text-[var(--text-faint)]">• dilaporkan {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($reportable instanceof \App\Models\Project)
                                    <h3 class="text-sm font-bold text-[var(--primary)] truncate">
                                        <a href="{{ route('projects.show', $reportable->slug) }}" target="_blank" class="hover:underline inline-flex items-center gap-1">
                                            <span>{{ $reportable->title }}</span>
                                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[var(--text-muted)]">
                                        Author: <a href="{{ route('profile.show', $reportable->user->username) }}" target="_blank" class="font-medium text-[var(--primary)] hover:underline">&#64;{{ $reportable->user->username }}</a>
                                    </p>
                                @elseif($reportable instanceof \App\Models\CommunityPost)
                                    <h3 class="text-sm font-bold text-[var(--primary)]">
                                        <span>Postingan Komunitas: </span>
                                        <span class="font-normal">{{ Str::limit($reportable->content, 90) }}</span>
                                    </h3>
                                    <p class="text-xs text-[var(--text-muted)]">
                                        Author: <a href="{{ route('profile.show', $reportable->user->username) }}" target="_blank" class="font-medium text-[var(--primary)] hover:underline">&#64;{{ $reportable->user->username }}</a>
                                        @if($reportable->community)
                                             · Komunitas: <a href="{{ route('communities.show', $reportable->community->slug) }}" target="_blank" class="hover:underline">{{ $reportable->community->name }}</a>
                                        @endif
                                    </p>
                                @elseif($reportable instanceof \App\Models\ProjectComment)
                                    <h3 class="text-sm font-bold text-[var(--primary)]">
                                        <span>Komentar: </span>
                                        <span class="font-normal">{{ Str::limit($reportable->body, 90) }}</span>
                                    </h3>
                                    <p class="text-xs text-[var(--text-muted)]">
                                        Author: <a href="{{ route('profile.show', $reportable->user->username) }}" target="_blank" class="font-medium text-[var(--primary)] hover:underline">&#64;{{ $reportable->user->username }}</a>
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-semibold italic">Konten telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 bg-[var(--surface-subtle)] p-2.5 rounded-xl border border-[var(--border-color)]">
                            <span class="text-[10px] text-[var(--text-muted)] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-bold text-[var(--primary)] hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium text-[var(--text-muted)]">Anonim / Guest</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs text-[var(--primary)]">
                            <span class="font-semibold text-[var(--text-muted)] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    @if($reportable instanceof \App\Models\Project && $reportable->warning_notes)
                        <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold block text-[10px] uppercase">Peringatan Terpasang:</span>
                                <span>{{ $reportable->warning_notes }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.projects.warn', $reportable->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[11px] font-bold text-amber-900 dark:text-amber-200 transition cursor-pointer">
                                    Cabut Peringatan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[var(--border-color)] text-xs">
                        <div class="flex items-center gap-2">
                            @if($reportable instanceof \App\Models\Project)
                                @if(!$reportable->warning_notes)
                                    <button type="button" @click="openWarn({{ $reportable->id }}, '{{ addslashes($reportable->title) }}')" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold border border-amber-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Beri Peringatan</span>
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('admin.projects.destroy', $reportable->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PROYEK ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Proyek</span>
                                    </button>
                                </form>
                            @elseif($reportable instanceof \App\Models\CommunityPost)
                                <form method="POST" action="{{ route('admin.community-posts.destroy', $reportable->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS POSTINGAN KOMUNITAS ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                        <span>Hapus Postingan</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] font-semibold border border-[var(--border-color)] transition cursor-pointer">
                                        Abaikan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-bold transition shadow-xs cursor-pointer">
                                        Tandai Selesai
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-[var(--text-faint)]">
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[var(--primary)]">Tidak ada laporan konten</h3>
                    <p class="text-xs text-[var(--text-muted)]">Semua konten dalam keadaan aman dan tidak memiliki laporan pelanggaran tertunda.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $projectReports->appends(['tab' => 'projects'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: LAPORAN AKUN -->
    @if($tab === 'users')
        <div class="space-y-3">
            @forelse($userReports as $report)
                @php $reportedUser = $report->reportable; @endphp
                <div class="bg-[var(--surface)] p-5 space-y-4 rounded-2xl border shadow-xs transition {{ $report->status ==='pending' ?'border-[var(--border-strong)] bg-[var(--surface-input)]' :'border-[var(--border-color)]' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            @if($reportedUser && $reportedUser->avatar)
                                <img src="{{ $reportedUser->avatar }}" alt="{{ $reportedUser->name }}" class="w-12 h-12 rounded-full object-cover shrink-0 border border-[var(--border-color)]">
                            @else
                                <div class="w-12 h-12 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-xs text-[var(--primary)] shrink-0 font-bold">
                                    {{ $reportedUser ? strtoupper(substr($reportedUser->name, 0, 2)) : '??' }}
                                </div>
                            @endif

                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $report->status === 'pending' ? 'bg-[var(--primary)] text-[var(--primary-text)]' : ($report->status === 'resolved' ? 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]') }}">
                                        {{ $report->status }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                                        {{ $report->reason_label }}
                                    </span>
                                    <span class="text-[11px] text-[var(--text-faint)]">• dilaporkan {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($reportedUser)
                                    <h3 class="text-sm font-bold text-[var(--primary)] truncate">
                                        <a href="{{ route('profile.show', $reportedUser->username) }}" target="_blank" class="hover:underline flex items-center gap-1.5">
                                            <span>{{ $reportedUser->name }}</span>
                                            <span class="text-xs font-mono text-[var(--text-muted)]">&#64;{{ $reportedUser->username }}</span>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[var(--text-muted)]">
                                        Email: {{ $reportedUser->email }} • Terdaftar {{ $reportedUser->created_at->format('d M Y') }}
                                        @if($reportedUser->is_banned)
                                            <span class="text-rose-600 dark:text-rose-400 font-bold ml-1">(Status: BANNED)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-semibold italic">Akun telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 bg-[var(--surface-subtle)] p-2.5 rounded-xl border border-[var(--border-color)]">
                            <span class="text-[10px] text-[var(--text-muted)] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-bold text-[var(--primary)] hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium text-[var(--text-muted)]">Anonim</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs text-[var(--primary)]">
                            <span class="font-semibold text-[var(--text-muted)] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[var(--border-color)] text-xs">
                        <div class="flex items-center gap-2">
                            @if($reportedUser && !$reportedUser->is_admin)
                                @if(!$reportedUser->is_banned)
                                    <button type="button" @click="openBan({{ $reportedUser->id }}, '{{ addslashes($reportedUser->name) }}')" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Ban Akun</span>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unban', $reportedUser->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-bold transition shadow-xs cursor-pointer">
                                            Buka Blokir (Unban)
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.destroy', $reportedUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh proyeknya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-[var(--surface-muted)] hover:bg-rose-500/10 hover:text-rose-600 text-[var(--text-muted)] font-semibold border border-[var(--border-color)] transition cursor-pointer">
                                        Hapus Akun Total
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] font-semibold border border-[var(--border-color)] transition cursor-pointer">
                                        Abaikan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-bold transition shadow-xs cursor-pointer">
                                        Tandai Selesai
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-[var(--text-faint)]">
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[var(--primary)]">Tidak ada laporan akun pengguna</h3>
                    <p class="text-xs text-[var(--text-muted)]">Semua profil pengguna beraktivitas normal tanpa ada laporan pelanggaran tertunda.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $userReports->appends(['tab' => 'users'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: AKUN DIBLOKIR (BANNED) -->
    @if($tab === 'banned')
        <div class="space-y-3">
            @forelse($bannedUsers as $bUser)
                <div class="bg-[var(--surface)] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-rose-500/20 bg-rose-500/[0.02] rounded-2xl shadow-xs">
                    <div class="flex items-center gap-3.5 min-w-0">
                        @if($bUser->avatar)
                            <img src="{{ $bUser->avatar }}" alt="{{ $bUser->name }}" class="w-12 h-12 rounded-full object-cover shrink-0 grayscale border border-rose-500/30">
                        @else
                            <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($bUser->name, 0, 2)) }}
                            </div>
                        @endif

                        <div class="space-y-0.5 min-w-0">
                            <h3 class="text-sm font-bold text-[var(--primary)] flex items-center gap-2 truncate">
                                <span>{{ $bUser->name }}</span>
                                <span class="text-xs font-mono text-[var(--text-muted)]">&#64;{{ $bUser->username }}</span>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-rose-500 text-white">Banned</span>
                            </h3>
                            <p class="text-xs text-[var(--text-muted)] truncate">
                                Alasan Ban: <span class="font-medium text-[var(--primary)]">{{ $bUser->ban_reason ?: 'Tidak disebutkan' }}</span>
                            </p>
                            <p class="text-[11px] text-[var(--text-faint)]">
                                Diblokir sejak: {{ $bUser->banned_at?->format('d M Y, H:i') ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('admin.users.unban', $bUser->id) }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] text-xs font-bold transition shadow-xs cursor-pointer">
                                Buka Blokir (Unban)
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $bUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold border border-rose-500/20 transition cursor-pointer">
                                Hapus Akun
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[var(--primary)]">Tidak ada akun yang diblokir</h3>
                    <p class="text-xs text-[var(--text-muted)]">Saat ini seluruh pengguna memiliki status akun aktif.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $bannedUsers->appends(['tab' => 'banned'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 4: KELOLA SEMUA PENGGUNA (AKSI LANGSUNG ADMIN) -->
    @if($tab === 'all_users')
        <div class="space-y-4">
            <!-- Search & Metric Bar -->
            <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto flex-1">
                    <input type="hidden" name="tab" value="all_users">
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#908F88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari nama, username, atau email pengguna..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none focus:border-[var(--primary)] transition">
                    </div>
                    <button type="submit" class="btn-primary text-xs py-2 px-4 cursor-pointer">
                        Cari
                    </button>
                    @if(!empty($searchQuery))
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="px-3 py-2 rounded-xl border border-[var(--border-color)] text-xs text-[var(--text-muted)] hover:bg-[var(--surface-muted)] transition">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-2 text-xs shrink-0 font-medium text-[var(--text-muted)]">
                    <span>Total: <strong class="text-[var(--primary)]">{{ $allUsersCount }}</strong></span>
                    <span>•</span>
                    <span class="text-amber-600 dark:text-amber-400">Peringatan: <strong>{{ $warnedUsersCount }}</strong></span>
                    <span>•</span>
                    <span class="text-rose-600 dark:text-rose-400">Diblokir: <strong>{{ $bannedUsersCount }}</strong></span>
                </div>
            </div>

            <!-- Users List -->
            <div class="space-y-3">
                @forelse($allUsers as $u)
                    <div class="bg-[var(--surface)] p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-2xl border shadow-xs transition {{ $u->is_banned ?'border-rose-500/30 bg-rose-500/[0.02]' : ($u->hasWarning() ?'border-amber-500/30 bg-amber-500/[0.02]' :'border-[var(--border-color)]') }}">
                        <!-- Left: User Profile Info -->
                        <div class="flex items-start gap-3.5 min-w-0">
                            <x-user-avatar :user="$u" size="lg" class="shrink-0" />
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-[var(--primary)] truncate">
                                        {{ $u->name }}
                                    </h3>
                                    <span class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $u->username }}</span>

                                    @if($u->is_admin)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-color)]">
                                            Admin
                                        </span>
                                    @endif

                                    @if($u->is_banned)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            Diblokir (Banned)
                                        </span>
                                    @elseif($u->hasWarning())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Dalam Peringatan
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                                            Aktif
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-[var(--text-muted)] flex items-center gap-2 flex-wrap">
                                    <span>{{ $u->email }}</span>
                                    <span>•</span>
                                    <span>{{ $u->projects_count }} Karya</span>
                                    <span>•</span>
                                    <span>Daftar {{ $u->created_at->format('d M Y') }}</span>
                                </p>

                                @if($u->hasWarning())
                                    <div class="mt-1.5 p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-200">
                                        <span class="font-bold text-[10px] uppercase block text-amber-700 dark:text-amber-300">Catatan Peringatan:</span>
                                        <p class="mt-0.5">{{ $u->warning_notes }}</p>
                                    </div>
                                @endif

                                @if($u->is_banned)
                                    <div class="mt-1.5 p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-900 dark:text-rose-200">
                                        <span class="font-bold text-[10px] uppercase block text-rose-700 dark:text-rose-300">Alasan Pemblokiran:</span>
                                        <p class="mt-0.5">{{ $u->ban_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Direct Admin Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end pt-2 md:pt-0 border-t md:border-t-0 border-[var(--border-color)]">
                            <!-- View Profile -->
                            <a href="{{ route('profile.show', $u->username) }}" target="_blank"
                               class="px-3 py-1.5 rounded-xl border border-[var(--border-color)] hover:bg-[var(--surface-muted)] text-xs font-semibold text-[var(--text-muted)] transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Profil</span>
                            </a>

                            @if(!$u->is_admin && $u->id !== auth()->id())
                                <!-- 1. Warning Button / Remove Warning -->
                                @if($u->hasWarning())
                                    <form method="POST" action="{{ route('admin.users.warn', $u->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-500/30 transition shadow-xs cursor-pointer">
                                            Cabut Peringatan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openUserWarn({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 text-xs font-bold border border-amber-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Beri Peringatan</span>
                                    </button>
                                @endif

                                <!-- 2. Ban / Unban Button -->
                                @if($u->is_banned)
                                    <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] text-xs font-bold transition shadow-xs cursor-pointer">
                                            Buka Blokir
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openBan({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-500/20 transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Blokir</span>
                                    </button>
                                @endif

                                <!-- 3. Delete User Button -->
                                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" onsubmit="return confirm('PERINGATAN ADMIN: Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh karyanya secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold border border-rose-500/20 transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-12 text-center space-y-3 shadow-xs">
                        <p class="text-sm font-semibold text-[var(--primary)]">Tidak ada pengguna yang cocok dengan pencarian.</p>
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="text-xs text-[var(--primary)] hover:underline font-semibold">Tampilkan Semua Pengguna</a>
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $allUsers->appends(['tab' => 'all_users', 'q' => $searchQuery ?? ''])->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL BERI PERINGATAN PROYEK -->
    <div x-show="warnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs" @click="warnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <h3 class="text-base font-bold">Pasang Peringatan Resmi pada Proyek</h3>
                <p class="text-xs text-[var(--text-muted)]">Proyek: <strong class="text-[var(--primary)]" x-text="warnProjectTitle"></strong></p>

                <form method="POST" :action="'/admin/project/' + warnProjectId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan Peringatan (Tampil di Proyek):</label>
                        <textarea name="warning_notes" x-model="warnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="warnModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs cursor-pointer">Pasang Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BERI PERINGATAN PENGGUNA -->
    <div x-show="userWarnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs" @click="userWarnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <h3 class="text-base font-bold text-amber-700 dark:text-amber-400">Beri Peringatan ke Akun Pengguna</h3>
                <p class="text-xs text-[var(--text-muted)]">Pengguna: <strong class="text-[var(--primary)]" x-text="userWarnName"></strong></p>

                <form method="POST" :action="'/admin/user/' + userWarnId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan Peringatan Akun:</label>
                        <textarea name="warning_notes" x-model="userWarnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                        <p class="text-[10px] text-[var(--text-muted)] mt-1">Peringatan ini akan ditampilkan pada profil akun tersebut.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="userWarnModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs cursor-pointer">Kirim Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BAN AKUN PENGGUNA -->
    <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs" @click="banModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <h3 class="text-base font-bold text-rose-600 dark:text-rose-400">Blokir Akun Pengguna (Ban)</h3>
                <p class="text-xs text-[var(--text-muted)]">Pengguna: <strong class="text-[var(--primary)]" x-text="banUserName"></strong></p>

                <form method="POST" :action="'/admin/user/' + banUserId + '/ban'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold mb-1">Alasan Penonaktifan Akun:</label>
                        <textarea name="ban_reason" x-model="banReason" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs outline-none focus:border-rose-500 transition resize-none"></textarea>
                        <p class="text-[10px] text-[var(--text-muted)] mt-1">Alasan ini akan ditampilkan kepada pengguna saat mereka mencoba login.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="banModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs cursor-pointer">Blokir Akun Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
