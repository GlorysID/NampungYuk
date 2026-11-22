@extends('layouts.app')

@section('title', 'Kelola ' . $community->name . ' — NampungYuk')
@section('header_title', 'Kelola Komunitas')
@section('main_width', 'max-w-5xl')
@section('hide_right_rail', '1')

@section('content')
@php
    $pendingTotal = $pendingRequests->count() + $pendingPosts->count();
    $roleLabel = \App\Models\CommunityMember::labelForRole($myRole);

    $existingQuestions = array_values(array_filter(
        (array) ($community->join_questions ?? []),
        fn ($q) => is_string($q) && trim($q) !== ''
    ));
    if (empty($existingQuestions)) {
        $existingQuestions = [];
    }

    $existingRules = $community->structuredRules();
@endphp

<div class="space-y-6">

    <!-- Top Back Bar & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1.5 min-w-0">
            <a href="{{ route('communities.show', $community->slug) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke {{ $community->name }}</span>
            </a>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--primary)] tracking-tight truncate">Kelola {{ $community->name }}</h1>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ in_array($myRole, ['owner', 'admin'], true) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : 'bg-[var(--surface-muted)] text-[var(--primary)] border-[var(--border-color)]' }}">
                    {{ $roleLabel }}
                </span>
            </div>
        </div>

        <a href="{{ route('communities.show', $community->slug) }}"
           class="px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] transition shrink-0 self-start sm:self-auto">
            Lihat Halaman Publik
        </a>
    </div>

    <!-- KPI Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Anggota</p>
            <p class="text-xl font-bold font-mono text-[var(--primary)] mt-1">{{ number_format($community->members_count) }}</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Permintaan Masuk</p>
            <p class="text-xl font-bold font-mono {{ $pendingRequests->count() > 0 ? 'text-amber-400' : 'text-[var(--primary)]' }} mt-1">{{ $pendingRequests->count() }}</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Postingan Tertunda</p>
            <p class="text-xl font-bold font-mono {{ $pendingPosts->count() > 0 ? 'text-amber-400' : 'text-[var(--primary)]' }} mt-1">{{ $pendingPosts->count() }}</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Total Postingan</p>
            <p class="text-xl font-bold font-mono text-[var(--primary)] mt-1">{{ number_format($community->posts_count) }}</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] col-span-2 sm:col-span-1">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-[var(--text-faint)]">Diblokir</p>
            <p class="text-xl font-bold font-mono text-[var(--primary)] mt-1">{{ $bans->count() }}</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-[var(--border-color)] pb-1 overflow-x-auto no-scrollbar">
        <a href="{{ route('communities.manage.index', ['community' => $community->slug, 'tab' => 'antrian']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $tab === 'antrian' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            <span>Antrian Persetujuan</span>
            @if($pendingTotal > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-[#0B0B0D] text-[10.5px] font-mono font-bold leading-none">{{ $pendingTotal }}</span>
            @endif
        </a>

        <a href="{{ route('communities.manage.index', ['community' => $community->slug, 'tab' => 'anggota']) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $tab === 'anggota' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            Anggota &amp; Blokir
        </a>

        @if($isAdmin)
            <a href="{{ route('communities.manage.index', ['community' => $community->slug, 'tab' => 'pengaturan']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $tab === 'pengaturan' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                Pengaturan Komunitas
            </a>
        @endif

        <a href="{{ route('communities.manage.index', ['community' => $community->slug, 'tab' => 'log']) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $tab === 'log' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
            Log Aktivitas
        </a>
    </div>

    <!-- Validation errors -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-xs sm:text-sm text-rose-400 space-y-1">
            @foreach($errors->all() as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    @if($tab === 'antrian')
        <!-- TAB 1: ANTRIAN PERSETUJUAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Join Requests Queue -->
            <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-[var(--border-color)] flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[var(--primary)]">Permintaan Bergabung</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Tinjau pendaftar dan jawaban pertanyaan masuk.</p>
                    </div>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)]">{{ $pendingRequests->count() }}</span>
                </div>

                <div class="divide-y divide-[var(--border-color)]">
                    @forelse($pendingRequests as $req)
                        <div class="p-4 sm:p-5 space-y-3">
                            <div class="flex items-start gap-3">
                                @if($req->user)
                                    <a href="{{ route('profile.show', $req->user->username) }}" class="shrink-0">
                                        <x-user-avatar :user="$req->user" size="md" />
                                    </a>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-[var(--primary)] truncate">{{ $req->user?->name ?? 'Pengguna' }}</p>
                                        <span class="text-[11px] font-mono text-[var(--text-faint)] shrink-0">{{ $req->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs font-mono text-[var(--text-muted)]">&#64;{{ $req->user?->username }}</p>
                                    @if($req->note)
                                        <p class="text-xs text-[var(--text-muted)] mt-2 p-2.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">{{ $req->note }}</p>
                                    @endif
                                </div>
                            </div>

                            @if(! empty($req->answers))
                                <div class="space-y-2 p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                                    @foreach($req->answers as $qa)
                                        <div class="text-xs space-y-0.5">
                                            <p class="font-semibold text-[var(--text-muted)]">{{ $qa['question'] ?? 'Pertanyaan' }}</p>
                                            <p class="text-[var(--primary)]">{{ $qa['answer'] ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <form method="POST" action="{{ route('communities.manage.requests.approve', [$community, $req]) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold cursor-pointer">
                                        Setujui
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('communities.manage.requests.reject', [$community, $req]) }}" class="flex items-center gap-2 flex-1 min-w-[200px]">
                                    @csrf
                                    <input type="text" name="rejection_reason" maxlength="255" placeholder="Alasan tolak (opsional)"
                                           class="flex-1 min-w-0 px-3 py-1.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-[var(--primary)]">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 text-xs font-semibold cursor-pointer shrink-0">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-[var(--text-muted)]">
                            Tidak ada permintaan bergabung yang menunggu.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Pending Posts Queue -->
            <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-[var(--border-color)] flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[var(--primary)]">Postingan Tertunda</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Setujui atau tolak postingan sebelum tampil di linimasa.</p>
                    </div>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)]">{{ $pendingPosts->count() }}</span>
                </div>

                <div class="divide-y divide-[var(--border-color)]">
                    @forelse($pendingPosts as $pp)
                        <div class="p-4 sm:p-5 space-y-3">
                            <div class="flex items-center gap-2.5">
                                <x-user-avatar :user="$pp->user" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-[var(--primary)] truncate">{{ $pp->user?->name ?? 'Anggota' }}</p>
                                    <p class="text-[11px] font-mono text-[var(--text-muted)]">{{ $pp->typeLabel() }} · {{ $pp->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <p class="text-sm text-[var(--primary)] leading-relaxed whitespace-pre-line p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">{{ $pp->content }}</p>

                            @if(count($pp->imageUrls()))
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($pp->imageUrls() as $imgUrl)
                                        <img src="{{ $imgUrl }}" alt="" class="w-full h-24 object-cover rounded-lg border border-[var(--border-color)]">
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <form method="POST" action="{{ route('communities.manage.posts.approve', [$community, $pp]) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold cursor-pointer">
                                        Terbitkan
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('communities.manage.posts.reject', [$community, $pp]) }}" class="flex items-center gap-2 flex-1 min-w-[200px]">
                                    @csrf
                                    <input type="text" name="rejection_reason" maxlength="255" placeholder="Alasan tolak (opsional)"
                                           class="flex-1 min-w-0 px-3 py-1.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-[var(--primary)]">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 text-xs font-semibold cursor-pointer shrink-0">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-[var(--text-muted)]">
                            Tidak ada postingan yang menunggu persetujuan.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

    @elseif($tab === 'anggota')
        <!-- TAB 2: ANGGOTA & BLOKIR -->
        <div class="space-y-5">
            <!-- Search & Role Filter -->
            <form method="GET" action="{{ route('communities.manage.index', $community->slug) }}" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="tab" value="anggota">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau @username anggota..."
                       class="flex-1 px-4 py-2.5 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] text-sm text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-[var(--primary)]">
                <select name="role" class="px-3.5 py-2.5 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                    <option value="">Semua Peran</option>
                    <option value="owner" {{ $roleFilter === 'owner' ? 'selected' : '' }}>Pemilik</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="mod" {{ $roleFilter === 'mod' ? 'selected' : '' }}>Moderator</option>
                    <option value="member" {{ $roleFilter === 'member' ? 'selected' : '' }}>Anggota</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] text-xs font-bold hover:bg-[var(--primary-hover)] cursor-pointer shrink-0">
                    Filter
                </button>
            </form>

            <!-- Members List -->
            <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[var(--primary)]">Daftar Anggota ({{ $members->total() }})</h2>
                </div>

                <div class="divide-y divide-[var(--border-color)]">
                    @foreach($members as $m)
                        @php
                            $targetRole = $m->pivot->role ?? 'member';
                            $isTargetMuted = $m->pivot->muted_until && \Illuminate\Support\Carbon::parse($m->pivot->muted_until)->isFuture();
                            $canChangeRole = $community->canManageRole(auth()->id(), $targetRole);
                            $canMuteTarget = $targetRole !== 'owner' && ($isOwner || ($isAdmin && $targetRole !== 'admin') || ($myRole === 'mod' && $targetRole === 'member'));
                            $canKickOrBan = $isAdmin && $targetRole !== 'owner' && ($isOwner || $targetRole !== 'admin');
                        @endphp
                        <div class="p-4 sm:px-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <a href="{{ route('profile.show', $m->username) }}" class="shrink-0">
                                    <x-user-avatar :user="$m" size="md" />
                                </a>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('profile.show', $m->username) }}" class="text-sm font-bold text-[var(--primary)] hover:underline truncate">{{ $m->name }}</a>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ in_array($targetRole, ['owner', 'admin'], true) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : 'bg-[var(--surface-subtle)] text-[var(--text-muted)] border-[var(--border-color)]' }}">
                                            {{ \App\Models\CommunityMember::labelForRole($targetRole) }}
                                        </span>
                                        @if($isTargetMuted)
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                Dibisukan
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-mono text-[var(--text-muted)] mt-0.5">&#64;{{ $m->username }}</p>
                                </div>
                            </div>

                            @if($targetRole !== 'owner')
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($canChangeRole)
                                        <form method="POST" action="{{ route('communities.manage.members.role', [$community, $m]) }}" class="inline-flex items-center gap-1.5">
                                            @csrf
                                            <select name="role" class="px-2.5 py-1.5 rounded-lg bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none">
                                                @if($isOwner)
                                                    <option value="admin" {{ $targetRole === 'admin' ? 'selected' : '' }}>Admin</option>
                                                @endif
                                                <option value="mod" {{ $targetRole === 'mod' ? 'selected' : '' }}>Moderator</option>
                                                <option value="member" {{ $targetRole === 'member' ? 'selected' : '' }}>Anggota</option>
                                            </select>
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] cursor-pointer">
                                                Simpan Peran
                                            </button>
                                        </form>
                                    @endif

                                    @if($canMuteTarget)
                                        @if($isTargetMuted)
                                            <form method="POST" action="{{ route('communities.manage.members.mute', [$community, $m]) }}">
                                                @csrf
                                                <input type="hidden" name="duration" value="unmute">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-amber-500/30 text-amber-300 hover:bg-amber-950/30 text-xs font-semibold cursor-pointer">
                                                    Lepas Bisu
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('communities.manage.members.mute', [$community, $m]) }}" class="inline-flex items-center gap-1">
                                                @csrf
                                                <select name="duration" class="px-2 py-1.5 rounded-lg bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none">
                                                    <option value="24h">Bisu 24j</option>
                                                    <option value="7d">Bisu 7h</option>
                                                </select>
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-amber-500/30 text-amber-300 hover:bg-amber-950/30 text-xs font-semibold cursor-pointer">
                                                    Bisukan
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if($canKickOrBan)
                                        <form method="POST" action="{{ route('communities.manage.members.kick', [$community, $m]) }}" onsubmit="return confirm('Keluarkan {{ addslashes($m->name) }} dari komunitas?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] text-xs font-semibold cursor-pointer">
                                                Keluarkan
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('communities.manage.members.ban', [$community, $m]) }}" onsubmit="return confirm('Blokir permanen {{ addslashes($m->name) }} dari komunitas ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 text-xs font-semibold cursor-pointer">
                                                Blokir
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($members->hasPages())
                    <div class="px-5 py-4 border-t border-[var(--border-color)]">{{ $members->links() }}</div>
                @endif
            </section>

            <!-- Banned Users -->
            @if($isAdmin)
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Daftar Pengguna Diblokir ({{ $bans->count() }})</h2>
                    </div>
                    <div class="divide-y divide-[var(--border-color)]">
                        @forelse($bans as $ban)
                            <div class="p-4 sm:px-5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($ban->user)
                                        <x-user-avatar :user="$ban->user" size="sm" />
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[var(--primary)] truncate">{{ $ban->user?->name ?? 'Pengguna' }}</p>
                                        <p class="text-xs font-mono text-[var(--text-muted)]">
                                            &#64;{{ $ban->user?->username }}
                                            @if($ban->reason)
                                                · Alasan: {{ $ban->reason }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($ban->user)
                                    <form method="POST" action="{{ route('communities.manage.bans.remove', [$community, $ban->user]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] cursor-pointer">
                                            Buka Blokir
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-[var(--text-muted)]">Belum ada akun yang diblokir dari komunitas ini.</div>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>

    @elseif($tab === 'pengaturan' && $isAdmin)
        <!-- TAB 3: PENGATURAN KOMUNITAS -->
        <div class="space-y-6">
            <form method="POST"
                  action="{{ route('communities.manage.settings.update', $community->slug) }}"
                  enctype="multipart/form-data"
                  class="space-y-6"
                  x-data="{
                      questions: {{ Illuminate\Support\Js::from($existingQuestions) }},
                      rules: {{ Illuminate\Support\Js::from($existingRules) }},
                      visibility: '{{ old('visibility', $community->visibility) }}',
                      joinPolicy: '{{ old('join_policy', $community->join_policy) }}',
                      addQuestion() {
                          if (this.questions.length < 3) this.questions.push('');
                      },
                      removeQuestion(idx) {
                          this.questions.splice(idx, 1);
                      },
                      addRule() {
                          if (this.rules.length < 10) this.rules.push({ title: '', description: '' });
                      },
                      removeRule(idx) {
                          this.rules.splice(idx, 1);
                      }
                  }">
                @csrf
                @method('PUT')

                <!-- Identitas & Media -->
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[var(--primary)]">Identitas &amp; Tampilan Komunitas</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Atur nama, deskripsi, ikon, dan gambar sampul komunitas.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Nama Komunitas</label>
                            <input type="text" name="name" required maxlength="60" value="{{ old('name', $community->name) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Topik / Kategori</label>
                            <input type="text" name="topic" maxlength="60" value="{{ old('topic', $community->topic) }}" placeholder="mis. Laravel, Web Dev, AI"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Lokasi (Opsional)</label>
                            <input type="text" name="location" maxlength="80" value="{{ old('location', $community->location) }}" placeholder="mis. Jakarta, Indonesia"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Website / Discord / GitHub (Opsional)</label>
                            <input type="url" name="website_url" maxlength="255" value="{{ old('website_url', $community->website_url) }}" placeholder="https://..."
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-[var(--primary)]">Deskripsi</label>
                        <textarea name="description" rows="3" maxlength="1000"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)] resize-none">{{ old('description', $community->description) }}</textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-[var(--primary)]">Pesan Sambutan Anggota Baru (Opsional)</label>
                        <textarea name="welcome_message" rows="2" maxlength="500" placeholder="Ditampilkan di atas linimasa untuk anggota..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)] resize-none">{{ old('welcome_message', $community->welcome_message) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[var(--border-color)]">
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-[var(--primary)]">Ikon Komunitas (Max 2MB)</label>
                            <input type="file" name="icon_file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-[var(--text-muted)] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-[var(--border-color)] file:bg-[var(--surface-subtle)] file:text-[var(--primary)] file:font-semibold">
                            @if($community->icon)
                                <label class="inline-flex items-center gap-2 text-xs text-rose-400 cursor-pointer">
                                    <input type="checkbox" name="remove_icon" value="1" class="rounded">
                                    <span>Hapus ikon saat ini</span>
                                </label>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-[var(--primary)]">Gambar Sampul / Cover (Max 4MB)</label>
                            <input type="file" name="cover_file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-[var(--text-muted)] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-[var(--border-color)] file:bg-[var(--surface-subtle)] file:text-[var(--primary)] file:font-semibold">
                            @if($community->cover)
                                <label class="inline-flex items-center gap-2 text-xs text-rose-400 cursor-pointer">
                                    <input type="checkbox" name="remove_cover" value="1" class="rounded">
                                    <span>Hapus cover saat ini</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Privasi, Kebijakan Gabung & Moderasi -->
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[var(--primary)]">Privasi &amp; Kebijakan Grup</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Kontrol siapa yang bisa melihat, bergabung, memposting, dan berkomentar.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Visibilitas</label>
                            <select name="visibility" x-model="visibility" @change="if (visibility === 'private') joinPolicy = 'approval'"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                                <option value="public">Publik</option>
                                <option value="private">Private</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Persetujuan Gabung</label>
                            <select name="join_policy" x-model="joinPolicy"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                                <option value="open">Langsung Gabung</option>
                                <option value="approval">Persetujuan Admin/Mod</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[var(--primary)]">Kebijakan Posting</label>
                            <select name="post_policy"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                                <option value="all_members" {{ old('post_policy', $community->post_policy) === 'all_members' ? 'selected' : '' }}>Semua Anggota</option>
                                <option value="approval_required" {{ old('post_policy', $community->post_policy) === 'approval_required' ? 'selected' : '' }}>Perlu Persetujuan Admin/Mod</option>
                                <option value="admins_only" {{ old('post_policy', $community->post_policy) === 'admins_only' ? 'selected' : '' }}>Hanya Admin &amp; Moderator</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-6 pt-2">
                        <label class="inline-flex items-center gap-2.5 text-sm text-[var(--primary)] cursor-pointer">
                            <input type="hidden" name="allow_member_comments" value="0">
                            <input type="checkbox" name="allow_member_comments" value="1" {{ old('allow_member_comments', $community->allow_member_comments) ? 'checked' : '' }} class="rounded">
                            <span>Izinkan anggota berkomentar di diskusi</span>
                        </label>

                        <label class="inline-flex items-center gap-2.5 text-sm text-[var(--primary)] cursor-pointer">
                            <input type="hidden" name="allow_member_images" value="0">
                            <input type="checkbox" name="allow_member_images" value="1" {{ old('allow_member_images', $community->allow_member_images) ? 'checked' : '' }} class="rounded">
                            <span>Izinkan anggota melampirkan gambar</span>
                        </label>
                    </div>
                </section>

                <!-- Pertanyaan Masuk -->
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[var(--primary)]">Pertanyaan Keanggotaan (Maks. 3)</h2>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Wajib dijawab calon anggota saat kebijakan gabung memakai persetujuan.</p>
                        </div>
                        <button type="button" @click="addQuestion()" x-show="questions.length < 3"
                                class="px-3 py-1.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] cursor-pointer shrink-0">
                            + Tambah Pertanyaan
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(q, idx) in questions" :key="idx">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono text-[var(--text-muted)] w-5 shrink-0" x-text="(idx + 1) + '.'"></span>
                                <input type="text" :name="'join_questions[' + idx + ']'" x-model="questions[idx]" maxlength="200"
                                       placeholder="Tulis pertanyaan untuk calon anggota..."
                                       class="flex-1 px-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                                <button type="button" @click="removeQuestion(idx)" class="px-2.5 py-2 rounded-xl border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 text-xs font-semibold cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </template>
                        <p x-show="questions.length === 0" class="text-xs text-[var(--text-muted)]">Belum ada pertanyaan masuk.</p>
                    </div>
                </section>

                <!-- Aturan Terstruktur -->
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[var(--primary)]">Aturan Komunitas Terstruktur (Maks. 10)</h2>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Ditampilkan di halaman Tentang &amp; Aturan serta gerbang komunitas privat.</p>
                        </div>
                        <button type="button" @click="addRule()" x-show="rules.length < 10"
                                class="px-3 py-1.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] cursor-pointer shrink-0">
                            + Tambah Aturan
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(rule, idx) in rules" :key="idx">
                            <div class="p-3.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-emerald-400 font-bold" x-text="'#' + (idx + 1)"></span>
                                    <input type="text" :name="'rules_items[' + idx + '][title]'" x-model="rule.title" maxlength="80"
                                           placeholder="Judul aturan (mis. Dilarang spam promosi)"
                                           class="flex-1 px-3 py-1.5 rounded-lg bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                                    <button type="button" @click="removeRule(idx)" class="px-2.5 py-1.5 rounded-lg border border-rose-500/30 text-rose-400 hover:bg-rose-950/30 text-xs font-semibold cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                                <input type="text" :name="'rules_items[' + idx + '][description]'" x-model="rule.description" maxlength="300"
                                       placeholder="Penjelasan singkat aturan (opsional)"
                                       class="w-full px-3 py-1.5 rounded-lg bg-[var(--surface-input)] border border-[var(--border-color)] text-xs text-[var(--primary)] outline-none focus:border-[var(--primary)]">
                            </div>
                        </template>
                        <p x-show="rules.length === 0" class="text-xs text-[var(--text-muted)]">Belum ada aturan yang ditambahkan.</p>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold transition shadow-xs cursor-pointer">
                        Simpan Semua Pengaturan
                    </button>
                </div>
            </form>

            <!-- Danger Zone (Owner Only) -->
            @if($isOwner)
                <section class="bg-[var(--surface)] border border-rose-500/30 rounded-2xl p-5 sm:p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-rose-400">Zona Berbahaya (Khusus Pemilik)</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Pindahkan hak milik grup atau hapus komunitas beserta seluruh diskusinya secara permanen.</p>
                    </div>

                    <!-- Transfer Ownership -->
                    <div class="p-4 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] space-y-3">
                        <p class="text-sm font-semibold text-[var(--primary)]">Pindahkan Kepemilikan Komunitas</p>
                        @if($adminCandidates->count() > 0)
                            <form method="POST" action="{{ route('communities.manage.transfer', $community) }}" class="flex flex-col sm:flex-row gap-2.5" onsubmit="return confirm('Yakin memindahkan kepemilikan komunitas ini? Peranmu akan menjadi Admin.')">
                                @csrf
                                <select name="new_owner_id" required class="flex-1 px-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none">
                                    @foreach($adminCandidates as $adm)
                                        <option value="{{ $adm->id }}">{{ $adm->name }} ({{ '@' . $adm->username }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-4 py-2 rounded-xl border border-amber-500/40 text-amber-300 hover:bg-amber-950/30 text-xs font-bold cursor-pointer shrink-0">
                                    Jadikan Pemilik
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-[var(--text-muted)]">Angkat minimal satu anggota menjadi <strong>Admin</strong> di tab Anggota sebelum memindahkan kepemilikan.</p>
                        @endif
                    </div>

                    <!-- Delete Community -->
                    <form method="POST" action="{{ route('communities.manage.destroy', $community) }}" class="p-4 rounded-xl bg-rose-950/20 border border-rose-500/30 space-y-3" onsubmit="return confirm('PERINGATAN: Hapus permanen komunitas {{ addslashes($community->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-rose-400">Hapus Komunitas Permanen</p>
                            <p class="text-xs text-[var(--text-muted)]">Ketik persis nama komunitas (<strong class="text-[var(--primary)] font-mono">{{ $community->name }}</strong>) untuk mengonfirmasi penghapusan.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2.5">
                            <input type="text" name="confirm_name" required placeholder="{{ $community->name }}"
                                   class="flex-1 px-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-sm text-[var(--primary)] outline-none focus:border-rose-400">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer shrink-0">
                                Hapus Komunitas
                            </button>
                        </div>
                    </form>
                </section>
            @endif
        </div>

    @elseif($tab === 'log')
        <!-- TAB 4: LOG AKTIVITAS -->
        <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[var(--border-color)] flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[var(--primary)]">Log Aktivitas Pengurus</h2>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5">50 catatan aksi moderasi dan perubahan pengaturan terbaru.</p>
                </div>
            </div>

            <div class="divide-y divide-[var(--border-color)]">
                @forelse($auditLogs as $log)
                    <div class="p-4 sm:px-5 flex items-start gap-3">
                        @if($log->actor)
                            <x-user-avatar :user="$log->actor" size="sm" />
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-[var(--primary)]">{{ $log->actor?->name ?? 'Sistem' }}</span>
                                <span class="text-[10.5px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)]">{{ $log->action }}</span>
                                <span class="text-xs font-mono text-[var(--text-faint)] ml-auto">{{ $log->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">{{ $log->summary }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-[var(--text-muted)]">Belum ada aktivitas moderasi yang tercatat.</div>
                @endforelse
            </div>
        </section>
    @endif

</div>
@endsection
