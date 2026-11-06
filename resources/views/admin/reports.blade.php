@extends('layouts.app')

@section('title', 'Moderasi & Pengguna — NampungYuk')

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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#e4e4e7] dark:border-[#27272a]">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-[#18181b] dark:text-[#fafafa]">Panel Moderasi & Pengguna</h1>
            <p class="text-xs text-[#71717a] dark:text-[#a1a1aa] mt-0.5">Kelola laporan pelanggaran karya, status moderasi, dan akun pengguna.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs px-3 py-1 rounded-full bg-white dark:bg-[#121214] border border-[#e4e4e7] dark:border-[#27272a] text-[#71717a] dark:text-[#a1a1aa] font-medium">
                {{ $pendingCount }} laporan menunggu
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="inline-flex items-center gap-1 p-1 rounded-2xl bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f] text-xs font-medium overflow-x-auto max-w-full">
        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl transition {{ $tab === 'all_users' ? 'bg-black dark:bg-white text-white dark:text-black font-semibold shadow-xs' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Kelola Pengguna</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'all_users' ? 'bg-white/20 dark:bg-black/10' : 'bg-black/5 dark:bg-white/10' }}">
                {{ $allUsersCount }}
            </span>
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'projects']) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl transition {{ $tab === 'projects' ? 'bg-black dark:bg-white text-white dark:text-black font-semibold shadow-xs' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Laporan Proyek</span>
            @if($pendingProjectReportsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-600 text-white font-bold">
                    {{ $pendingProjectReportsCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'users']) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl transition {{ $tab === 'users' ? 'bg-black dark:bg-white text-white dark:text-black font-semibold shadow-xs' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Laporan Akun</span>
            @if($pendingUserReportsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-600 text-white font-bold">
                    {{ $pendingUserReportsCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.reports.index', ['tab' => 'banned']) }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl transition {{ $tab === 'banned' ? 'bg-black dark:bg-white text-white dark:text-black font-semibold shadow-xs' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
            <span>Akun Diblokir</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'banned' ? 'bg-white/20 dark:bg-black/10' : 'bg-black/5 dark:bg-white/10' }}">
                {{ $bannedUsersCount }}
            </span>
        </a>
    </div>

    <!-- TAB 1: LAPORAN PROYEK -->
    @if($tab === 'projects')
        <div class="space-y-3">
            @forelse($projectReports as $report)
                @php $project = $report->reportable; @endphp
                <div class="ny-card p-4 sm:p-5 space-y-4 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <!-- Report Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            @if($project && $project->thumbnail)
                                <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-[#e4e4e7] dark:border-[#27272a]">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-black/5 dark:bg-white/5 border border-[#e4e4e7] dark:border-[#27272a] flex items-center justify-center text-xs font-mono text-[#71717a] shrink-0">
                                    [App]
                                </div>
                            @endif

                            <div class="space-y-1.5 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- Status Laporan: Kuning Solid Menunggu, Hijau Solid Selesai, Gelap Ditolak --}}
                                    @if($report->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-amber-500 text-white shadow-xs">
                                            Menunggu
                                        </span>
                                    @elseif($report->status === 'resolved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-emerald-600 text-white shadow-xs">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-zinc-600 dark:bg-zinc-700 text-white">
                                            Ditolak
                                        </span>
                                    @endif

                                    {{-- Alasan Pelanggaran: Merah Solid --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-rose-600 text-white shadow-xs">
                                        {{ $report->reason_label }}
                                    </span>

                                    <span class="text-[11px] text-[#71717a]">&bull; {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($project)
                                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white">
                                        <a href="{{ route('projects.show', $project->slug) }}" target="_blank" class="hover:underline inline-flex items-center gap-1">
                                            <span>{{ $project->title }}</span>
                                            <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[#71717a]">
                                        Author: <a href="{{ route('profile.show', $project->user->username) }}" target="_blank" class="font-medium text-[#18181b] dark:text-[#fafafa] hover:underline">&#64;{{ $project->user->username }}</a>
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-medium italic">Proyek telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 text-[#71717a]">
                            <span class="text-[10px] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-medium text-[#18181b] dark:text-white hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium">Anonim</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes & Warning Information -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-[#f4f4f5] dark:bg-[#121214] text-xs text-[#18181b] dark:text-[#fafafa]">
                            <span class="font-medium text-[#71717a] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    @if($project && $project->warning_notes)
                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-[#18181b] dark:text-[#fafafa] flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold block text-[10px] text-amber-600 dark:text-amber-400 uppercase">Peringatan Terpasang:</span>
                                <span>{{ $project->warning_notes }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.projects.warn', $project->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="px-3 py-1 rounded-full border border-amber-500/40 bg-white dark:bg-zinc-900 text-[11px] font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-500/10 transition">
                                    Cabut
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="pt-3 border-t border-[#e4e4e7] dark:border-[#27272a] space-y-3">
                        <!-- Row 1: Target Actions (Proyek & Pelaku) -->
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($project)
                                <a href="{{ route('projects.show', $project->slug) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs font-semibold shadow-xs transition">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Buka Proyek</span>
                                </a>

                                @if(!$project->warning_notes)
                                    <button type="button" @click="openWarn({{ $project->id }}, '{{ addslashes($project->title) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-amber-500 hover:text-amber-500 dark:hover:text-amber-400 dark:hover:border-amber-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Peringatkan</span>
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('admin.projects.destroy', $project->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PROYEK ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Proyek</span>
                                    </button>
                                </form>

                                @if($project->user && !$project->user->is_admin && !$project->user->is_banned)
                                    <button type="button" @click="openBan({{ $project->user->id }}, '{{ addslashes($project->user->name) }} (&#64;{{ addslashes($project->user->username) }})')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Blokir User</span>
                                    </button>
                                @endif
                            @endif
                        </div>

                        <!-- Row 2: Resolution Decision -->
                        <div class="flex items-center justify-between gap-3 pt-2.5 border-t border-dashed border-[#e4e4e7] dark:border-[#27272a] flex-wrap">
                            <span class="text-[11px] text-[#71717a]">
                                @if($report->status === 'pending')
                                    Keputusan Laporan:
                                @else
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                @endif
                            </span>

                            <div class="flex items-center gap-2">
                                @if($report->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Abaikan Laporan</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Tandai Selesai</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-2 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white">Tidak ada laporan proyek</h3>
                    <p class="text-xs text-[#71717a]">Semua proyek dalam keadaan aman dan tidak memiliki laporan pelanggaran tertunda.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $projectReports->appends(['tab' => 'projects'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: LAPORAN AKUN PENGGUNA -->
    @if($tab === 'users')
        <div class="space-y-3">
            @forelse($userReports as $report)
                @php $reportedUser = $report->reportable; @endphp
                <div class="ny-card p-4 sm:p-5 space-y-4 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <!-- Report Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            @if($reportedUser)
                                <x-user-avatar :user="$reportedUser" size="lg" class="shrink-0" />
                            @else
                                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 border border-[#e4e4e7] dark:border-[#27272a] flex items-center justify-center text-xs font-mono text-[#71717a] shrink-0">
                                    ??
                                </div>
                            @endif

                            <div class="space-y-1.5 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($report->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-amber-500 text-white shadow-xs">
                                            Menunggu
                                        </span>
                                    @elseif($report->status === 'resolved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-emerald-600 text-white shadow-xs">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-zinc-600 dark:bg-zinc-700 text-white">
                                            Ditolak
                                        </span>
                                    @endif

                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-rose-600 text-white shadow-xs">
                                        {{ $report->reason_label }}
                                    </span>

                                    <span class="text-[11px] text-[#71717a]">&bull; {{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                @if($reportedUser)
                                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white">
                                        <a href="{{ route('profile.show', $reportedUser->username) }}" target="_blank" class="hover:underline flex items-center gap-1.5">
                                            <span>{{ $reportedUser->name }}</span>
                                            <span class="text-xs font-mono text-[#71717a]">&#64;{{ $reportedUser->username }}</span>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-[#71717a]">
                                        Email: {{ $reportedUser->email }} &bull; Terdaftar {{ $reportedUser->created_at->format('d M Y') }}
                                        @if($reportedUser->is_banned)
                                            <span class="text-rose-600 font-bold ml-1">(BANNED)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs text-rose-500 font-medium italic">Akun telah dihapus dari sistem.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Reporter Info -->
                        <div class="text-left sm:text-right text-xs shrink-0 text-[#71717a]">
                            <span class="text-[10px] block">Pelapor:</span>
                            @if($report->reporter)
                                <a href="{{ route('profile.show', $report->reporter->username) }}" class="font-medium text-[#18181b] dark:text-white hover:underline">
                                    &#64;{{ $report->reporter->username }}
                                </a>
                            @else
                                <span class="font-medium">Anonim</span>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($report->notes)
                        <div class="p-3 rounded-xl bg-[#f4f4f5] dark:bg-[#121214] text-xs text-[#18181b] dark:text-[#fafafa]">
                            <span class="font-medium text-[#71717a] text-[10px] block uppercase mb-0.5">Catatan Pelapor:</span>
                            {{ $report->notes }}
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="pt-3 border-t border-[#e4e4e7] dark:border-[#27272a] space-y-3">
                        <!-- Row 1: Target Actions (Akun Pengguna) -->
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($reportedUser && !$reportedUser->is_admin)
                                <a href="{{ route('profile.show', $reportedUser->username) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs font-semibold shadow-xs transition">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Profil</span>
                                </a>

                                @if(!$reportedUser->is_banned)
                                    <button type="button" @click="openBan({{ $reportedUser->id }}, '{{ addslashes($reportedUser->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Blokir Akun</span>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unban', $reportedUser->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Buka Blokir</span>
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.destroy', $reportedUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh karyanya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Akun</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Row 2: Resolution Decision -->
                        <div class="flex items-center justify-between gap-3 pt-2.5 border-t border-dashed border-[#e4e4e7] dark:border-[#27272a] flex-wrap">
                            <span class="text-[11px] text-[#71717a]">
                                @if($report->status === 'pending')
                                    Keputusan Laporan:
                                @else
                                    Ditangani {{ $report->resolved_at?->diffForHumans() }}
                                @endif
                            </span>

                            <div class="flex items-center gap-2">
                                @if($report->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Abaikan Laporan</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Tandai Selesai</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-2 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white">Tidak ada laporan akun pengguna</h3>
                    <p class="text-xs text-[#71717a]">Semua profil pengguna beraktivitas normal tanpa ada laporan pelanggaran tertunda.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $userReports->appends(['tab' => 'users'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: AKUN DIBLOKIR (BANNED) -->
    @if($tab === 'banned')
        <div class="space-y-3">
            @forelse($bannedUsers as $bUser)
                <div class="ny-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <div class="flex items-center gap-3">
                        <x-user-avatar :user="$bUser" size="lg" class="shrink-0 grayscale opacity-75" />

                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-[#18181b] dark:text-white flex items-center gap-2">
                                <span>{{ $bUser->name }}</span>
                                <span class="text-xs font-mono text-[#71717a]">&#64;{{ $bUser->username }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-xs">Banned</span>
                            </h3>
                            <p class="text-xs text-[#71717a]">
                                Alasan: <span class="text-[#18181b] dark:text-[#fafafa] font-medium">{{ $bUser->ban_reason ?: 'Tidak disebutkan' }}</span>
                            </p>
                            <p class="text-[11px] text-[#71717a]">
                                Diblokir: {{ $bUser->banned_at?->format('d M Y, H:i') ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.users.unban', $bUser->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Buka Blokir</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $bUser->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS TOTAL akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Akun</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="ny-card p-12 text-center space-y-2 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white">Tidak ada akun yang diblokir</h3>
                    <p class="text-xs text-[#71717a]">Saat ini seluruh pengguna memiliki status akun aktif.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $bannedUsers->appends(['tab' => 'banned'])->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 4: KELOLA SEMUA PENGGUNA -->
    @if($tab === 'all_users')
        <div class="space-y-4">
            <!-- Search & Metric Bar -->
            <div class="ny-card p-3 sm:p-4 flex flex-col md:flex-row items-center justify-between gap-3 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto flex-1">
                    <input type="hidden" name="tab" value="all_users">
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#71717a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari nama, username, atau email..." class="w-full pl-9 pr-3 py-1.5 rounded-full bg-[#f4f4f5] dark:bg-[#121214] border border-zinc-300 dark:border-zinc-700 text-xs text-[#18181b] dark:text-white outline-none focus:border-neutral-500 transition">
                    </div>
                    <button type="submit" class="px-4 py-1.5 rounded-full bg-black dark:bg-white text-white dark:text-black text-xs font-semibold hover:bg-neutral-800 dark:hover:bg-neutral-200 transition">
                        Cari
                    </button>
                    @if(!empty($searchQuery))
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 text-xs font-medium text-[#71717a] hover:bg-black/5 dark:hover:bg-white/5 transition">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-3 text-xs text-[#71717a] font-normal shrink-0">
                    <span><strong class="font-semibold text-[#18181b] dark:text-white">{{ $allUsersCount }}</strong> pengguna</span>
                    <span>&bull;</span>
                    <span><strong class="font-semibold text-amber-500">{{ $warnedUsersCount }}</strong> peringatan</span>
                    <span>&bull;</span>
                    <span><strong class="font-semibold text-rose-500">{{ $bannedUsersCount }}</strong> diblokir</span>
                </div>
            </div>

            <!-- Users List -->
            <div class="space-y-3">
                @forelse($allUsers as $u)
                    <div class="ny-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                        <!-- Left: User Profile Info -->
                        <div class="flex items-start gap-3 min-w-0">
                            <x-user-avatar :user="$u" size="lg" class="shrink-0" />
                            <div class="min-w-0 space-y-1.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-semibold text-[#18181b] dark:text-white truncate">
                                        {{ $u->name }}
                                    </h3>
                                    <span class="text-xs font-mono text-[#71717a] truncate">&#64;{{ $u->username }}</span>

                                    @if($u->is_admin)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-800 text-white border border-zinc-700">
                                            Admin
                                        </span>
                                    @endif

                                    @if($u->is_banned)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white shadow-xs">
                                            Banned
                                        </span>
                                    @elseif($u->hasWarning())
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs">
                                            Peringatan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-xs">
                                            Aktif
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-[#71717a] flex items-center gap-2 flex-wrap">
                                    <span>{{ $u->email }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $u->projects_count }} Karya</span>
                                    <span>&bull;</span>
                                    <span>Daftar {{ $u->created_at->format('d M Y') }}</span>
                                </p>

                                @if($u->hasWarning())
                                    <div class="mt-1.5 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-[#18181b] dark:text-[#fafafa]">
                                        <span class="font-bold text-[10px] uppercase block text-amber-600 dark:text-amber-400">Peringatan Terpasang:</span>
                                        <p class="mt-0.5">{{ $u->warning_notes }}</p>
                                    </div>
                                @endif

                                @if($u->is_banned && $u->ban_reason)
                                    <div class="mt-1.5 p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-[#18181b] dark:text-[#fafafa]">
                                        <span class="font-bold text-[10px] uppercase block text-rose-600 dark:text-rose-400">Alasan Pemblokiran:</span>
                                        <p class="mt-0.5">{{ $u->ban_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end pt-2 md:pt-0 border-t md:border-t-0 border-[#e4e4e7] dark:border-[#27272a]">
                            <a href="{{ route('profile.show', $u->username) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Profil</span>
                            </a>

                            @if(!$u->is_admin && $u->id !== auth()->id())
                                @if($u->hasWarning())
                                    <form method="POST" action="{{ route('admin.users.warn', $u->id) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-amber-500/40 bg-white dark:bg-zinc-900 text-amber-600 dark:text-amber-400 hover:bg-amber-500/10 text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Cabut Peringatan</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openUserWarn({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-amber-500 hover:text-amber-500 dark:hover:text-amber-400 dark:hover:border-amber-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Peringatan</span>
                                    </button>
                                @endif

                                @if($u->is_banned)
                                    <form method="POST" action="{{ route('admin.users.unban', $u->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Buka Blokir</span>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" @click="openBan({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <span>Blokir</span>
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" onsubmit="return confirm('PERINGATAN ADMIN: Anda yakin ingin MENGHAPUS TOTAL akun ini beserta seluruh karyanya secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:border-rose-500 hover:text-rose-500 dark:hover:text-rose-400 dark:hover:border-rose-500 text-zinc-800 dark:text-zinc-200 text-xs font-semibold shadow-xs transition group">
                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ny-card p-12 text-center space-y-2 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                        <p class="text-sm font-semibold text-[#18181b] dark:text-white">Tidak ada pengguna yang cocok dengan pencarian.</p>
                        <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" class="text-xs text-[#0070f3] dark:text-[#3291ff] hover:underline font-medium">Tampilkan Semua Pengguna</a>
                    </div>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $allUsers->appends(['tab' => 'all_users', 'q' => $searchQuery])->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL PERINGATAN PROYEK -->
    <div x-show="warnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" @click="warnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#121214] border border-[#e4e4e7] dark:border-[#27272a] rounded-2xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <div>
                    <h3 class="text-base font-semibold text-[#18181b] dark:text-white">Pasang Peringatan pada Proyek</h3>
                    <p class="text-xs text-[#71717a] mt-0.5">Proyek: <strong class="text-[#18181b] dark:text-white" x-text="warnProjectTitle"></strong></p>
                </div>

                <form method="POST" :action="'/admin/project/' + warnProjectId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-medium text-[#71717a] mb-1">Catatan Peringatan:</label>
                        <textarea name="warning_notes" x-model="warnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f4f4f5] dark:bg-[#18181b] border border-zinc-300 dark:border-zinc-700 text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="warnModalOpen = false" class="px-4 py-2 rounded-full border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-[#18181b] dark:hover:text-white transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition">Simpan Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BERI PERINGATAN PENGGUNA -->
    <div x-show="userWarnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" @click="userWarnModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#121214] border border-[#e4e4e7] dark:border-[#27272a] rounded-2xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <div>
                    <h3 class="text-base font-semibold text-[#18181b] dark:text-white">Beri Peringatan ke Akun</h3>
                    <p class="text-xs text-[#71717a] mt-0.5">Pengguna: <strong class="text-[#18181b] dark:text-white" x-text="userWarnName"></strong></p>
                </div>

                <form method="POST" :action="'/admin/user/' + userWarnId + '/warning'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set">
                    <div>
                        <label class="block text-xs font-medium text-[#71717a] mb-1">Catatan Peringatan Akun:</label>
                        <textarea name="warning_notes" x-model="userWarnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f4f4f5] dark:bg-[#18181b] border border-zinc-300 dark:border-zinc-700 text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="userWarnModalOpen = false" class="px-4 py-2 rounded-full border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-[#18181b] dark:hover:text-white transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition">Kirim Peringatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BAN AKUN PENGGUNA -->
    <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" @click="banModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#121214] border border-[#e4e4e7] dark:border-[#27272a] rounded-2xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                <div>
                    <h3 class="text-base font-semibold text-rose-600 dark:text-rose-400">Blokir Akun Pengguna</h3>
                    <p class="text-xs text-[#71717a] mt-0.5">Pengguna: <strong class="text-[#18181b] dark:text-white" x-text="banUserName"></strong></p>
                </div>

                <form method="POST" :action="'/admin/user/' + banUserId + '/ban'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-[#71717a] mb-1">Alasan Pemblokiran:</label>
                        <textarea name="ban_reason" x-model="banReason" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f4f4f5] dark:bg-[#18181b] border border-zinc-300 dark:border-zinc-700 text-xs outline-none focus:border-rose-500 transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="banModalOpen = false" class="px-4 py-2 rounded-full border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-[#18181b] dark:hover:text-white transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">Blokir Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
