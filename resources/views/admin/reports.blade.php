@extends('layouts.app')

@section('title', 'Panel Moderasi — NampungYuk')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[#e4e4e7] dark:border-[#27272a]">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-500 text-[10px] font-bold uppercase tracking-wider mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Administrator Space</span>
            </div>
            <h1 class="text-2xl font-black text-[#18181b] dark:text-[#fafafa] tracking-tight">Panel Moderasi & Pengguna</h1>
            <p class="text-xs text-[#71717a] mt-1">Kelola pengguna secara langsung, tinjau laporan, beri peringatan, atau blokir akun yang melanggar.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 font-bold">
                {{ $pendingCount }} Laporan Menunggu
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-[#e4e4e7] dark:border-[#27272a] pb-px overflow-x-auto text-xs font-semibold">
        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'all_users' ? 'border-[#0070f3] text-[#0070f3] dark:text-[#3291ff] bg-black/5 dark:bg-white/5 font-bold' : 'border-transparent text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Kelola Semua Pengguna</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/10 dark:bg-white/10">
                {{ $allUsersCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'projects']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'projects' ? 'border-[#0070f3] text-[#0070f3] dark:text-[#3291ff] bg-black/5 dark:bg-white/5 font-bold' : 'border-transparent text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Laporan Proyek</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $pendingProjectReportsCount > 0 ? 'bg-rose-500 text-white' : 'bg-black/10 dark:bg-white/10' }}">
                {{ $pendingProjectReportsCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'users']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'users' ? 'border-[#0070f3] text-[#0070f3] dark:text-[#3291ff] bg-black/5 dark:bg-white/5 font-bold' : 'border-transparent text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Laporan Akun</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $pendingUserReportsCount > 0 ? 'bg-rose-500 text-white' : 'bg-black/10 dark:bg-white/10' }}">
                {{ $pendingUserReportsCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'banned']) }}" 
           class="px-4 py-2.5 rounded-t-xl transition flex items-center gap-2 border-b-2 {{ $tab === 'banned' ? 'border-[#0070f3] text-[#0070f3] dark:text-[#3291ff] bg-black/5 dark:bg-white/5 font-bold' : 'border-transparent text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Akun Diblokir</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/10 dark:bg-white/10">
                {{ $bannedUsersCount }}
            </span>
        </a>
    </div>

    <!-- TAB 1: LAPORAN PROYEK -->
    @if($tab === 'projects')
        <div class="space-y-3">
            @forelse($projectReports as $report)
                @php $project = $report->reportable; @endphp
                <div class="ny-card p-5 space-y-4 border {{ $report->status === 'pending' ? 'border-amber-500/40 bg-amber-500/[0.02]' : 'border-[#e4e4e7] dark:border-[#27272a]' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            @if($project && $project->thumbnail)
                                <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-[#e4e4e7] dark:border-[#27272a]">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-black/5 dark:bg-white/5 border border-[#e4e4e7] dark:border-[#27272a] flex items-center justify-center text-xs text-[#71717a] shrink-0 font-mono">
                                    [App]
                                </div>
                            @endif

                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $report->status === 'pending' ? 'bg-amber-500 text-white' : ($report->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-black/10 text-[#71717a]') }}">
                                        {{ $report->status }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                                        {{ $report->reason_label }}
                                    </span>
                                    <span class="text-[11px] text-[#71717a]">• dilaporkan {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($project)
                                    <h3 class="text-sm font-bold text-[#18181b] dark:text-white">
                                        <a href="{{ route('projects.show', $project->slug) }}" target="_blank" class="hover:text-[#0070f3] dark:hover:text-[#3291ff] hover:underline inline-flex items-center gap-1">
                                            <span>{{ $project->title }}</span>
                                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[#71717a]">
                                        Author: <a href="{{ route('profile.show', $project->user->username) }}" target="_blank" class="font-medium text-[#18181b] dark:text-[#fafafa] hover:underline">&#64;{{ $project->user->username }}</a>
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-semibold italic">Proyek telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 bg-black/5 dark:bg-white/5 p-2.5 rounded-xl border border-black/5 dark:border-white/5">
                            <span class="text-[10px] text-[#71717a] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-bold text-[#18181b] dark:text-white hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium text-[#71717a]">Anonim / Guest</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-black/5 dark:bg-white/5 text-xs text-[#18181b] dark:text-[#fafafa]">
                            <span class="font-semibold text-[#71717a] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    @if($project && $project->warning_notes)
                        <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold block text-[10px] uppercase">Peringatan Terpasang:</span>
                                <span>{{ $project->warning_notes }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.projects.warn', $project->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-black/10 hover:bg-black/20 dark:bg-white/10 dark:hover:bg-white/20 text-[11px] font-bold text-amber-800 dark:text-amber-200 transition">
                                    Cabut Peringatan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a] text-xs">
                        <div class="flex items-center gap-2">
                            @if($project)
                                @if(!$project->warning_notes)
                                    <button type="button" @click="openWarn({{ $project->id }}, '{{ addslashes($project->title) }}')" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold border border-amber-500/20 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Beri Peringatan</span>
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('admin.projects.destroy', $project->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PROYEK ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/20 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Proyek</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 text-[#71717a] font-semibold border border-[#e4e4e7] dark:border-[#27272a] transition">
                                        Abaikan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                                        Tandai Selesai
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-[#71717a]">
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[#18181b] dark:text-white">Tidak ada laporan proyek</h3>
                    <p class="text-xs text-[#71717a]">Semua proyek dalam keadaan aman dan tidak memiliki laporan pelanggaran tertunda.</p>
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
                <div class="ny-card p-5 space-y-4 border {{ $report->status === 'pending' ? 'border-amber-500/40 bg-amber-500/[0.02]' : 'border-[#e4e4e7] dark:border-[#27272a]' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            @if($reportedUser && $reportedUser->avatar)
                                <img src="{{ $reportedUser->avatar }}" alt="{{ $reportedUser->name }}" class="w-12 h-12 rounded-full object-cover shrink-0 border border-[#e4e4e7] dark:border-[#27272a]">
                            @else
                                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 border border-[#e4e4e7] dark:border-[#27272a] flex items-center justify-center text-xs text-[#71717a] shrink-0 font-bold">
                                    {{ $reportedUser ? strtoupper(substr($reportedUser->name, 0, 2)) : '??' }}
                                </div>
                            @endif

                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $report->status === 'pending' ? 'bg-amber-500 text-white' : ($report->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-black/10 text-[#71717a]') }}">
                                        {{ $report->status }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                                        {{ $report->reason_label }}
                                    </span>
                                    <span class="text-[11px] text-[#71717a]">• dilaporkan {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($reportedUser)
                                    <h3 class="text-sm font-bold text-[#18181b] dark:text-white">
                                        <a href="{{ route('profile.show', $reportedUser->username) }}" target="_blank" class="hover:underline flex items-center gap-1.5">
                                            <span>{{ $reportedUser->name }}</span>
                                            <span class="text-xs font-mono text-[#71717a]">&#64;{{ $reportedUser->username }}</span>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[#71717a]">
                                        Email: {{ $reportedUser->email }} • Terdaftar {{ $reportedUser->created_at->format('d M Y') }}
                                        @if($reportedUser->is_banned)
                                            <span class="text-rose-500 font-bold ml-1">(Status: BANNED)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-semibold italic">Akun telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 bg-black/5 dark:bg-white/5 p-2.5 rounded-xl border border-black/5 dark:border-white/5">
                            <span class="text-[10px] text-[#71717a] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-bold text-[#18181b] dark:text-white hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium text-[#71717a]">Anonim</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-black/5 dark:bg-white/5 text-xs text-[#18181b] dark:text-[#fafafa]">
                            <span class="font-semibold text-[#71717a] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a] text-xs">
                        <div class="flex items-center gap-2">
                            @if($reportedUser && !$reportedUser->is_admin)
                                @if(!$reportedUser->is_banned)
                                    <button type="button" @click="openBan({{ $reportedUser->id }}, '{{ addslashes($reportedUser->name) }}')" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/20 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Ban Akun</span>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unban', $reportedUser->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20 transition">
                                            Buka Blokir (Unban)
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.destroy', $reportedUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh proyeknya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-black/5 hover:bg-rose-500/10 hover:text-rose-600 text-[#71717a] font-semibold border border-[#e4e4e7] dark:border-[#27272a] transition">
                                        Hapus Akun Total
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 text-[#71717a] font-semibold border border-[#e4e4e7] dark:border-[#27272a] transition">
                                        Abaikan
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                                        Tandai Selesai
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-[#71717a]">
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[#18181b] dark:text-white">Tidak ada laporan akun pengguna</h3>
                    <p class="text-xs text-[#71717a]">Semua profil pengguna beraktivitas normal tanpa ada laporan pelanggaran tertunda.</p>
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
                <div class="ny-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-rose-500/20 bg-rose-500/[0.02]">
                    <div class="flex items-center gap-3.5">
                        @if($bUser->avatar)
                            <img src="{{ $bUser->avatar }}" alt="{{ $bUser->name }}" class="w-12 h-12 rounded-full object-cover shrink-0 grayscale border border-rose-500/30">
                        @else
                            <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($bUser->name, 0, 2)) }}
                            </div>
                        @endif

                        <div class="space-y-0.5">
                            <h3 class="text-sm font-bold text-[#18181b] dark:text-white flex items-center gap-2">
                                <span>{{ $bUser->name }}</span>
                                <span class="text-xs font-mono text-[#71717a]">&#64;{{ $bUser->username }}</span>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-rose-500 text-white">Banned</span>
                            </h3>
                            <p class="text-xs text-[#71717a]">
                                Alasan Ban: <span class="font-medium text-[#18181b] dark:text-[#fafafa]">{{ $bUser->ban_reason ?: 'Tidak disebutkan' }}</span>
                            </p>
                            <p class="text-[11px] text-[#71717a]">
                                Diblokir sejak: {{ $bUser->banned_at?->format('d M Y, H:i') ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.users.unban', $bUser->id) }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                                Buka Blokir (Unban)
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $bUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold border border-rose-500/20 transition">
                                Hapus Akun
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-[#18181b] dark:text-white">Tidak ada akun yang diblokir</h3>
                    <p class="text-xs text-[#71717a]">Saat ini seluruh pengguna memiliki status akun aktif.</p>
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
            <div class="ny-card p-4 flex flex-col md:flex-row items-center justify-between gap-3 bg-white dark:bg-[#0a0a0a]">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto flex-1">
                    <input type="hidden" name="tab" value="all_users">
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#71717a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari nama, username, atau email pengguna..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs text-[#18181b] dark:text-white outline-none focus:border-[#0070f3] transition">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0070f3] text-white text-xs font-bold hover:bg-[#0060df] transition">
                        Cari
                    </button>
                    @if(!empty($searchQuery))
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="px-3 py-2 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] text-xs text-[#71717a] hover:bg-black/5 dark:hover:bg-white/5 transition">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-2 text-xs shrink-0 font-medium text-[#71717a]">
                    <span>Total Pengguna: <strong class="text-[#18181b] dark:text-white">{{ $allUsersCount }}</strong></span>
                    <span>•</span>
                    <span class="text-amber-600 dark:text-amber-400">Peringatan: <strong>{{ $warnedUsersCount }}</strong></span>
                    <span>•</span>
                    <span class="text-rose-600 dark:text-rose-400">Diblokir: <strong>{{ $bannedUsersCount }}</strong></span>
                </div>
            </div>

            <!-- Users List -->
            <div class="space-y-3">
                @forelse($allUsers as $u)
                    <div class="ny-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border {{ $u->is_banned ? 'border-rose-500/30 bg-rose-500/[0.02]' : ($u->hasWarning() ? 'border-amber-500/30 bg-amber-500/[0.02]' : 'border-[#e4e4e7] dark:border-[#27272a]') }}">
                        <!-- Left: User Profile Info -->
                        <div class="flex items-start gap-3.5 min-w-0">
                            <x-user-avatar :user="$u" size="lg" class="shrink-0" />
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-[#18181b] dark:text-white truncate">
                                        {{ $u->name }}
                                    </h3>
                                    <span class="text-xs font-mono text-[#71717a] truncate">&#64;{{ $u->username }}</span>

                                    @if($u->is_admin)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
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
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            Aktif
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-[#71717a] flex items-center gap-2 flex-wrap">
                                    <span>{{ $u->email }}</span>
                                    <span>•</span>
                                    <span>{{ $u->projects_count }} Karya</span>
                                    <span>•</span>
                                    <span>Daftar {{ $u->created_at->format('d M Y') }}</span>
                                </p>

                                @if($u->hasWarning())
                                    <div class="mt-1.5 p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-200">
                                        <span class="font-bold text-[10px] uppercase block text-amber-700 dark:text-amber-300">Catatan Peringatan:</span>
                                        <p class="mt-0.5">{{ $u->warning_notes }}</p>
                                    </div>
                                @endif

                                @if($u->is_banned)
                                    <div class="mt-1.5 p-2 rounded-lg bg-rose-500/10 border border-rose-500/20 text-xs text-rose-900 dark:text-rose-200">
                                        <span class="font-bold text-[10px] uppercase block text-rose-700 dark:text-rose-300">Alasan Pemblokiran:</span>
                                        <p class="mt-0.5">{{ $u->ban_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Direct Admin Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end pt-2 md:pt-0 border-t md:border-t-0 border-[#e4e4e7] dark:border-[#27272a]">
                            <!-- View Profile -->
                            <a href="{{ route('profile.show', $u->username) }}" target="_blank"
                               class="px-3 py-1.5 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 text-xs font-semibold text-[#71717a] hover:text-[#18181b] dark:hover:text-white transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Profil</span>
                            </a>

                            @if(!$u->is_admin && $u->id !== auth()->id())
                                <!-- 1. Warning Button / Remove Warning -->
                                @if($u->hasWarning())
                                    <form method="POST" action="{{ route('admin.users.warn', $u->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-500/30 transition shadow-sm">
                                            Cabut Peringatan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openUserWarn({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-bold border border-amber-500/20 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Beri Peringatan</span>
                                    </button>
                                @endif

                                <!-- 2. Ban / Unban Button -->
                                @if($u->is_banned)
                                    <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                                            Buka Blokir
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openBan({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-500/20 transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Blokir</span>
                                    </button>
                                @endif

                                <!-- 3. Delete User Button -->
                                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" onsubmit="return confirm('PERINGATAN ADMIN: Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh karyanya secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold border border-rose-500/20 transition">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ny-card p-12 text-center space-y-3">
                        <p class="text-sm font-semibold text-[#18181b] dark:text-white">Tidak ada pengguna yang cocok dengan pencarian.</p>
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="text-xs text-[#0070f3] dark:text-[#3291ff] hover:underline font-semibold">Tampilkan Semua Pengguna</a>
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
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md" @click="warnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#0c0d12] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <h3 class="text-base font-bold">Pasang Peringatan Resmi pada Proyek</h3>
                <p class="text-xs text-[#71717a]">Proyek: <strong class="text-[#18181b] dark:text-white" x-text="warnProjectTitle"></strong></p>

                <form method="POST" :action="'/admin/project/' + warnProjectId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan Peringatan (Tampil di Proyek):</label>
                        <textarea name="warning_notes" x-model="warnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="warnModalOpen = false" class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-xs font-semibold text-[#71717a]">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm">Pasang Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BERI PERINGATAN PENGGUNA -->
    <div x-show="userWarnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md" @click="userWarnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#0c0d12] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <h3 class="text-base font-bold text-amber-600 dark:text-amber-400">Beri Peringatan ke Akun Pengguna</h3>
                <p class="text-xs text-[#71717a]">Pengguna: <strong class="text-[#18181b] dark:text-white" x-text="userWarnName"></strong></p>

                <form method="POST" :action="'/admin/user/' + userWarnId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Catatan Peringatan Akun:</label>
                        <textarea name="warning_notes" x-model="userWarnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                        <p class="text-[10px] text-[#71717a] mt-1">Peringatan ini akan ditampilkan pada profil akun tersebut.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="userWarnModalOpen = false" class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-xs font-semibold text-[#71717a]">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm">Kirim Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BAN AKUN PENGGUNA -->
    <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md" @click="banModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#0c0d12] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <h3 class="text-base font-bold text-rose-600 dark:text-rose-400">Blokir Akun Pengguna (Ban)</h3>
                <p class="text-xs text-[#71717a]">Pengguna: <strong class="text-[#18181b] dark:text-white" x-text="banUserName"></strong></p>

                <form method="POST" :action="'/admin/user/' + banUserId + '/ban'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold mb-1">Alasan Penonaktifan Akun:</label>
                        <textarea name="ban_reason" x-model="banReason" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs outline-none focus:border-rose-500 transition resize-none"></textarea>
                        <p class="text-[10px] text-[#71717a] mt-1">Alasan ini akan ditampilkan kepada pengguna saat mereka mencoba login.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="banModalOpen = false" class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-xs font-semibold text-[#71717a]">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm">Blokir Akun Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
