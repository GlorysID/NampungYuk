@extends('layouts.app')

@section('title', $community->name . ' — Komunitas NampungYuk')
@section('header_title', $community->name)
@section('meta_description', $community->description ?: 'Komunitas developer di NampungYuk.')

@section('content')
@php
    $joinQuestions = array_values(array_filter(
        (array) ($community->join_questions ?? []),
        fn ($q) => is_string($q) && trim($q) !== ''
    ));
    $structuredRules = $community->structuredRules();
    $safeWebsiteUrl = (is_string($community->website_url) && preg_match('#^https?://#i', $community->website_url))
        ? $community->website_url
        : null;
    $myRoleLabel = $myRole ? \App\Models\CommunityMember::labelForRole($myRole) : null;
    $allowComments = $isMember && ! $isMuted && ($community->allow_member_comments || $isModerator);
    $canAttachImages = $community->allow_member_images || $isModerator;
@endphp

<div class="space-y-5 max-w-3xl mx-auto" x-data="{ joinModalOpen: false }">

    <!-- Community header -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-xs">
        <div class="h-28 sm:h-36 relative bg-[var(--bg-app)]">
            @if($community->cover)
                <img src="{{ $community->cover }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-r from-[var(--surface-subtle)] via-[var(--surface-muted)] to-[var(--surface-subtle)]"></div>
            @endif
        </div>

        <div class="px-5 pb-5 -mt-8 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="flex items-end gap-4 flex-1 min-w-0">
                    @if($community->icon)
                        <img src="{{ $community->icon }}" alt="" class="w-16 sm:w-20 h-16 sm:h-20 rounded-2xl object-cover ring-4 ring-[var(--surface)] bg-[var(--surface)] border border-[var(--border-color)] shrink-0">
                    @else
                        <div class="w-16 sm:w-20 h-16 sm:h-20 rounded-2xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center text-2xl font-bold ring-4 ring-[var(--surface)] shrink-0">{{ mb_substr($community->name, 0, 1) }}</div>
                    @endif

                    <div class="flex-1 min-w-0 pb-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-bold text-[var(--primary)] truncate">{{ $community->name }}</h1>
                            @if($community->topic)
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">{{ $community->topic }}</span>
                            @endif
                            <span class="text-xs font-bold uppercase {{ $community->isPrivate() ? 'text-amber-400' : 'text-emerald-400' }}">
                                {{ $community->isPrivate() ? 'Private' : 'Publik' }}
                            </span>
                            @if($community->requiresJoinApproval() && ! $community->isPrivate())
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)]">Perlu Persetujuan</span>
                            @endif
                            @if($myRoleLabel)
                                <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border {{ in_array($myRole, ['owner', 'admin'], true) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : 'bg-[var(--surface-subtle)] text-[var(--primary)] border-[var(--border-color)]' }}">
                                    {{ $myRoleLabel }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">
                            {{ number_format($community->members_count) }} anggota · {{ number_format($community->posts_count) }} postingan · dibuat oleh {{ $community->owner?->name }}
                            @if($community->location)
                                · {{ $community->location }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if($isModerator && ! $isBanned)
                        <a href="{{ route('communities.manage.index', $community->slug) }}"
                           class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold py-2.5 px-4 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] hover:border-[var(--border-strong)] text-[var(--primary)] transition shadow-xs">
                            <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Kelola Komunitas</span>
                            @if(($pendingModerationCount ?? 0) > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-[#0B0B0D] text-[10.5px] font-mono font-bold leading-none">{{ $pendingModerationCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if(! $isBanned)
                        @auth
                            @if($isOwner)
                                <span class="text-xs font-semibold py-2.5 px-4 rounded-xl bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                                    Pemilik Grup
                                </span>
                            @elseif($isMember)
                                <button type="button" x-data="communityJoin({ joined: true, pending: false, url: '{{ route('communities.join', $community) }}' })"
                                        @click="toggle()" :disabled="loading"
                                        :class="joined ? 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]' : 'bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)]'"
                                        class="text-sm font-semibold py-2.5 px-5 rounded-xl transition disabled:opacity-60 shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]"
                                        x-text="joined ? 'Bergabung' : (pending ? 'Menunggu Persetujuan' : '{{ $community->requiresJoinApproval() ? 'Minta Bergabung' : 'Gabung' }}')"></button>
                            @elseif($pendingJoinRequest)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold py-2.5 px-4 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                        Menunggu Persetujuan
                                    </span>
                                    <form method="POST" action="{{ route('communities.join.cancel', $community) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold py-2.5 px-3 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @elseif($community->requiresJoinApproval() && ! empty($joinQuestions))
                                <button type="button" @click="joinModalOpen = true"
                                        class="text-sm font-semibold py-2.5 px-5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] transition shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]">
                                    Minta Bergabung
                                </button>
                            @else
                                <button type="button" x-data="communityJoin({ joined: false, pending: false, url: '{{ route('communities.join', $community) }}' })"
                                        @click="toggle()" :disabled="loading"
                                        :class="joined ? 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]' : (pending ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)]')"
                                        class="text-sm font-semibold py-2.5 px-5 rounded-xl transition disabled:opacity-60 shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]"
                                        x-text="joined ? 'Bergabung' : (pending ? 'Menunggu Persetujuan' : '{{ $community->requiresJoinApproval() ? 'Minta Bergabung' : 'Gabung' }}')"></button>
                            @endif
                        @else
                            <button type="button" @click="window.openAuthModal('login')" class="text-sm font-semibold py-2.5 px-5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] transition shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]">
                                {{ $community->requiresJoinApproval() ? 'Minta Bergabung' : 'Gabung' }}
                            </button>
                        @endauth
                    @endif
                </div>
            </div>

            @if($community->description && ! $isLockedPrivate && ! $isBanned && $section !== 'tentang')
                <div class="mt-4 p-4 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">Tentang komunitas ini</p>
                    <p class="text-sm sm:text-[14.5px] text-[var(--primary)] leading-relaxed whitespace-pre-line">{{ $community->description }}</p>
                    @if($safeWebsiteUrl)
                        <a href="{{ $safeWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-medium text-emerald-400 hover:underline">
                            <span>{{ parse_url($safeWebsiteUrl, PHP_URL_HOST) ?: $safeWebsiteUrl }}</span>
                        </a>
                    @endif
                </div>
            @endif

            @if(! $isLockedPrivate && ! $isBanned && $members->count())
                <div class="flex items-center gap-2 mt-3.5">
                    <div class="flex -space-x-2">
                        @foreach($members->take(6) as $m)
                            <x-user-avatar :user="$m" size="xs" class="ring-2 ring-[var(--surface)]" />
                        @endforeach
                    </div>
                    <a href="{{ route('communities.show', ['slug' => $community->slug, 'section' => 'anggota']) }}" class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                        {{ number_format($community->members_count) }} anggota bergabung
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- BANNED STATE --}}
    @if($isBanned)
        <div class="bg-[var(--surface)] border border-rose-500/30 rounded-2xl p-6 text-center space-y-2 shadow-xs">
            <h2 class="text-base font-bold text-rose-400">Akses Komunitas Dibatasi</h2>
            <p class="text-sm text-[var(--text-muted)]">Akun kamu diblokir dari komunitas ini oleh pengurus.</p>
            <div class="pt-2">
                <a href="{{ route('communities.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                    Kembali ke Daftar Komunitas
                </a>
            </div>
        </div>

    {{-- PRIVATE GATE --}}
    @elseif($isLockedPrivate)
        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 space-y-4 shadow-xs">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-mono uppercase tracking-wider text-amber-400 font-semibold">
                    Komunitas Private
                </span>
                <h2 class="text-lg font-bold text-[var(--primary)]">Konten ini hanya untuk anggota komunitas</h2>
                <p class="text-sm text-[var(--text-muted)] leading-relaxed">
                    {{ $community->description ?: 'Ajukan permintaan bergabung untuk membaca diskusi, bertanya, dan berkolaborasi dengan anggota di dalamnya.' }}
                </p>
            </div>

            @if(! empty($structuredRules))
                <div class="p-4 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] space-y-2.5">
                    <p class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Aturan Komunitas</p>
                    <ol class="space-y-2">
                        @foreach($structuredRules as $idx => $rule)
                            <li class="text-xs sm:text-sm text-[var(--primary)]">
                                <span class="font-mono text-[var(--text-muted)] mr-1.5">{{ $idx + 1 }}.</span>
                                <span class="font-semibold">{{ $rule['title'] }}</span>
                                @if($rule['description'])
                                    <p class="text-xs text-[var(--text-muted)] pl-5 mt-0.5">{{ $rule['description'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @auth
                @if($pendingJoinRequest)
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-xs sm:text-sm text-amber-300">Permintaan bergabungmu sedang ditinjau oleh pengurus komunitas.</p>
                        <form method="POST" action="{{ route('communities.join.cancel', $community) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] cursor-pointer">
                                Batalkan Permintaan
                            </button>
                        </form>
                    </div>
                @elseif(! empty($joinQuestions))
                    <button type="button" @click="joinModalOpen = true"
                            class="px-5 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold transition cursor-pointer">
                        Isi Pertanyaan &amp; Minta Bergabung
                    </button>
                @else
                    <form method="POST" action="{{ route('communities.join', $community) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold transition cursor-pointer">
                            Kirim Permintaan Bergabung
                        </button>
                    </form>
                @endif
            @else
                <button type="button" @click="window.openAuthModal('login')" class="px-5 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold transition cursor-pointer">
                    Masuk untuk Minta Bergabung
                </button>
            @endauth
        </div>

    {{-- FULL COMMUNITY WORKSPACE --}}
    @else
        <!-- Primary Section Tabs: Diskusi | Anggota | Tentang & Aturan -->
        <div class="flex items-center gap-2 border-b border-[var(--border-color)] pb-1 overflow-x-auto no-scrollbar">
            <a href="{{ route('communities.show', ['slug' => $community->slug, 'section' => 'diskusi']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $section === 'diskusi' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                Diskusi
            </a>
            <a href="{{ route('communities.show', ['slug' => $community->slug, 'section' => 'anggota']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $section === 'anggota' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                Anggota ({{ number_format($community->members_count) }})
            </a>
            <a href="{{ route('communities.show', ['slug' => $community->slug, 'section' => 'tentang']) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold transition whitespace-nowrap {{ $section === 'tentang' ? 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                Tentang &amp; Aturan
            </a>
        </div>

        @if($section === 'anggota')
            <!-- TAB: ANGGOTA -->
            <div class="space-y-4">
                @if($isAdmin)
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)]">
                        <p class="text-xs sm:text-sm text-[var(--text-muted)]">Ingin mengatur peran, membisukan, mengeluarkan, atau memblokir anggota?</p>
                        <a href="{{ route('communities.manage.index', ['community' => $community->slug, 'tab' => 'anggota']) }}"
                           class="px-3.5 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] text-xs font-bold hover:bg-[var(--primary-hover)] transition shrink-0">
                            Kelola Anggota
                        </a>
                    </div>
                @endif

                @if(($adminModerators ?? collect())->count() > 0)
                    <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                            <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-[var(--text-muted)]">Admin &amp; Moderator</h2>
                            <span class="text-xs font-mono text-[var(--text-faint)]">{{ $adminModerators->count() }}</span>
                        </div>
                        <div class="divide-y divide-[var(--border-color)]">
                            @foreach($adminModerators as $staff)
                                <a href="{{ route('profile.show', $staff->username) }}" class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-[var(--surface-hover)] transition">
                                    <x-user-avatar :user="$staff" size="md" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-[var(--primary)] truncate">{{ $staff->name }}</p>
                                        <p class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $staff->username }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ in_array($staff->pivot->role, ['owner', 'admin'], true) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : 'bg-[var(--surface-subtle)] text-[var(--primary)] border-[var(--border-color)]' }}">
                                        {{ \App\Models\CommunityMember::labelForRole($staff->pivot->role) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                        <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-[var(--text-muted)]">Daftar Anggota</h2>
                        <span class="text-xs font-mono text-[var(--text-faint)]">{{ number_format($community->members_count) }}</span>
                    </div>
                    <div class="divide-y divide-[var(--border-color)]">
                        @forelse($allMembers as $mem)
                            <div class="flex items-center gap-3.5 px-5 py-3.5">
                                <a href="{{ route('profile.show', $mem->username) }}" class="shrink-0">
                                    <x-user-avatar :user="$mem" size="md" />
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('profile.show', $mem->username) }}" class="text-sm font-bold text-[var(--primary)] hover:underline truncate block">{{ $mem->name }}</a>
                                    <p class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $mem->username }}</p>
                                </div>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)]">
                                    {{ \App\Models\CommunityMember::labelForRole($mem->pivot->role) }}
                                </span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-[var(--text-muted)]">Belum ada anggota.</div>
                        @endforelse
                    </div>
                    @if($allMembers instanceof \Illuminate\Pagination\AbstractPaginator && $allMembers->hasPages())
                        <div class="px-5 py-4 border-t border-[var(--border-color)]">{{ $allMembers->links() }}</div>
                    @endif
                </section>
            </div>

        @elseif($section === 'tentang')
            <!-- TAB: TENTANG & ATURAN -->
            <div class="space-y-4">
                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-[var(--primary)]">Tentang {{ $community->name }}</h2>
                        <p class="text-sm text-[var(--text-muted)] leading-relaxed whitespace-pre-line mt-2">
                            {{ $community->description ?: 'Belum ada deskripsi untuk komunitas ini.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-[var(--border-color)] text-xs">
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            <p class="font-mono uppercase tracking-wider text-[10.5px] text-[var(--text-faint)]">Visibilitas</p>
                            <p class="font-semibold text-[var(--primary)] mt-1">{{ $community->isPrivate() ? 'Private (hanya anggota)' : 'Publik (terlihat semua orang)' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            <p class="font-mono uppercase tracking-wider text-[10.5px] text-[var(--text-faint)]">Izin Bergabung</p>
                            <p class="font-semibold text-[var(--primary)] mt-1">{{ $community->requiresJoinApproval() ? 'Perlu persetujuan admin/mod' : 'Terbuka langsung gabung' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            <p class="font-mono uppercase tracking-wider text-[10.5px] text-[var(--text-faint)]">Kebijakan Posting</p>
                            <p class="font-semibold text-[var(--primary)] mt-1">
                                {{ match($community->post_policy) {
                                    'approval_required' => 'Perlu persetujuan admin/mod',
                                    'admins_only' => 'Hanya admin & moderator',
                                    default => 'Semua anggota boleh posting',
                                } }}
                            </p>
                        </div>
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            <p class="font-mono uppercase tracking-wider text-[10.5px] text-[var(--text-faint)]">Interaksi Anggota</p>
                            <p class="font-semibold text-[var(--primary)] mt-1">
                                {{ $community->allow_member_comments ? 'Komentar aktif' : 'Komentar terbatas' }} · {{ $community->allow_member_images ? 'Gambar diizinkan' : 'Tanpa gambar anggota' }}
                            </p>
                        </div>
                    </div>

                    @if($community->location || $safeWebsiteUrl)
                        <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-[var(--text-muted)]">
                            @if($community->location)
                                <span>Lokasi: <strong class="text-[var(--primary)]">{{ $community->location }}</strong></span>
                            @endif
                            @if($safeWebsiteUrl)
                                <a href="{{ $safeWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:underline font-medium">
                                    {{ $safeWebsiteUrl }}
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 space-y-3">
                    <h3 class="text-sm font-bold text-[var(--primary)]">Aturan Komunitas</h3>
                    @if(! empty($structuredRules))
                        <div class="divide-y divide-[var(--border-color)]">
                            @foreach($structuredRules as $idx => $rule)
                                <div class="py-3 first:pt-0 last:pb-0">
                                    <p class="text-sm font-semibold text-[var(--primary)]">
                                        <span class="font-mono text-emerald-400 mr-1.5">{{ $idx + 1 }}.</span>{{ $rule['title'] }}
                                    </p>
                                    @if($rule['description'])
                                        <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1 pl-5 leading-relaxed">{{ $rule['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs sm:text-sm text-[var(--text-muted)]">Belum ada aturan tertulis yang ditambahkan pengurus.</p>
                    @endif
                </section>
            </div>

        @else
            <!-- TAB: DISKUSI -->
            @if($community->welcome_message && $isMember)
                <div x-data="{
                         shown: false,
                         init() {
                             try {
                                 this.shown = localStorage.getItem('ny_comm_welcome_{{ $community->id }}') !== '1';
                             } catch (e) {
                                 this.shown = true;
                             }
                         },
                         dismiss() {
                             this.shown = false;
                             try {
                                 localStorage.setItem('ny_comm_welcome_{{ $community->id }}', '1');
                             } catch (e) {}
                         }
                     }"
                     x-show="shown"
                     x-cloak
                     class="p-4 rounded-2xl bg-[var(--surface)] border border-emerald-500/25 flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <p class="text-[11px] font-mono font-bold uppercase tracking-wider text-emerald-400">Pesan Sambutan</p>
                        <p class="text-sm text-[var(--primary)] leading-relaxed whitespace-pre-line">{{ $community->welcome_message }}</p>
                    </div>
                    <button type="button"
                            @click="dismiss()"
                            aria-label="Tutup pesan sambutan"
                            class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] px-2 py-1 rounded-lg hover:bg-[var(--surface-hover)] shrink-0 cursor-pointer">
                        Tutup
                    </button>
                </div>
            @endif

            @if($isMuted && $memberRecord)
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs sm:text-sm text-amber-300 space-y-1">
                    <p class="font-semibold">Akunmu sedang dibisukan hingga {{ $memberRecord->muted_until->translatedFormat('d M Y H:i') }}.</p>
                    @if($memberRecord->mute_reason)
                        <p class="text-xs text-amber-200/80">Alasan: {{ $memberRecord->mute_reason }}</p>
                    @endif
                </div>
            @endif

            @if(($myPendingPosts ?? collect())->count() > 0)
                <div class="p-4 rounded-2xl bg-[var(--surface)] border border-amber-500/30 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-400">Postingan Menunggu Persetujuan ({{ $myPendingPosts->count() }})</p>
                    </div>
                    @foreach($myPendingPosts->take(3) as $mp)
                        <div class="p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] text-xs text-[var(--text-muted)] flex items-center justify-between gap-3">
                            <span class="truncate">{{ mb_strimwidth($mp->content, 0, 120, '…') }}</span>
                            <span class="shrink-0 font-mono text-[10.5px] text-amber-400">Menunggu</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Sort tabs + search -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="inline-flex items-center p-1 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] text-xs shrink-0">
                    @foreach(['terbaru' => 'Terbaru', 'populer' => 'Terpopuler', 'terjawab' => 'Terjawab'] as $key => $label)
                        <a href="{{ route('communities.show', array_filter(['slug' => $community->slug, 'section' => 'diskusi', 'tab' => $key, 'q' => $search])) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === $key ? 'bg-[var(--surface)] text-[var(--primary)] shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('communities.show', $community->slug) }}" class="relative flex-1">
                    @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
                    <svg class="w-4 h-4 text-[var(--text-muted)] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari di komunitas ini..." class="w-full pl-10 pr-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-xs sm:text-sm outline-none focus:border-[var(--primary)] shadow-xs transition">
                </form>
            </div>

            <!-- Post composer (members only) -->
            @auth
                @if($isMember)
                    @if($isMuted)
                        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 text-center text-xs text-[var(--text-muted)] shadow-xs">
                            Kamu tidak dapat membuat postingan selama masa pembisuan berlangsung.
                        </div>
                    @elseif($community->post_policy === 'admins_only' && ! $isModerator)
                        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 text-center text-xs text-[var(--text-muted)] shadow-xs">
                            Hanya admin dan moderator yang dapat membuat postingan di komunitas ini.
                        </div>
                    @else
                        <form method="POST"
                              action="{{ route('communities.post', $community) }}"
                              enctype="multipart/form-data"
                              class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 space-y-3 shadow-xs"
                              x-data="{
                                  type: 'discussion',
                                  files: [],
                                  maxFiles: 4,
                                  handleImages(event) {
                                      const picked = Array.from(event.target.files || []).filter(f => f && f.type && f.type.startsWith('image/'));
                                      if (! picked.length) return;
                                      const remaining = this.maxFiles - this.files.length;
                                      if (remaining <= 0) {
                                          if (window.notify) notify('Maksimal 4 foto per postingan.');
                                          this.syncInput();
                                          return;
                                      }
                                      if (picked.length > remaining && window.notify) {
                                          notify('Maksimal 4 foto. Hanya ' + remaining + ' foto tambahan yang dimasukkan.');
                                      }
                                      for (const file of picked.slice(0, remaining)) {
                                          this.files.push({ file, url: URL.createObjectURL(file) });
                                      }
                                      this.syncInput();
                                  },
                                  removeImage(idx) {
                                      const removed = this.files.splice(idx, 1)[0];
                                      if (removed && removed.url) {
                                          try { URL.revokeObjectURL(removed.url); } catch (e) {}
                                      }
                                      this.syncInput();
                                  },
                                  syncInput() {
                                      const input = this.$refs.communityImagesInput;
                                      if (! input) return;
                                      const dt = new DataTransfer();
                                      for (const item of this.files) {
                                          dt.items.add(item.file);
                                      }
                                      input.files = dt.files;
                                  }
                              }">
                            @csrf
                            @if($community->postsRequireApproval(auth()->id()))
                                <p class="text-[11px] font-medium text-amber-400">Postingan baru akan ditinjau admin/moderator sebelum tampil di linimasa.</p>
                            @endif
                            <div class="flex gap-3">
                                <x-user-avatar :user="auth()->user()" size="sm" />
                                <textarea name="content" required maxlength="2000" rows="2" placeholder="Bagikan ilmu, ajukan pertanyaan, atau mulai diskusi..."
                                          class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm resize-none outline-none focus:border-[var(--primary)] transition flex-1"></textarea>
                            </div>
                            @if($canAttachImages)
                                <div x-show="files.length > 0" x-cloak class="pl-10 space-y-1.5">
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <template x-for="(item, idx) in files" :key="item.url">
                                            <div class="relative aspect-square rounded-xl overflow-hidden border border-[var(--border-color)] bg-[var(--surface-muted)]">
                                                <img :src="item.url" alt="" class="w-full h-full object-cover">
                                                <button type="button"
                                                        @click="removeImage(idx)"
                                                        aria-label="Hapus foto"
                                                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-[11px] font-mono text-[var(--text-muted)]" x-text="files.length + '/4 foto dipilih · klik Foto lagi untuk tambah'"></p>
                                </div>
                            @endif
                            <input type="hidden" name="type" :value="type">
                            <div class="flex flex-wrap items-center justify-between gap-2 pl-10">
                                <div class="flex items-center gap-1.5">
                                    @foreach(['discussion' => 'Diskusi', 'question' => 'Tanya Jawab'] as $k => $lbl)
                                        <button type="button" @click="type = '{{ $k }}'"
                                                :class="type === '{{ $k }}' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] hover:text-[var(--primary)]'"
                                                class="text-[11px] font-medium px-3 py-1 rounded-full transition cursor-pointer">{{ $lbl }}</button>
                                    @endforeach
                                    @if($isModerator)
                                        <button type="button" @click="type = 'announcement'"
                                                :class="type === 'announcement' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold shadow-xs' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] hover:text-[var(--primary)]'"
                                                class="text-[11px] font-medium px-3 py-1 rounded-full transition cursor-pointer">Pengumuman</button>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($canAttachImages)
                                        <label class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] cursor-pointer hover:text-[var(--primary)] transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Foto</span>
                                            <span x-show="files.length > 0" x-cloak class="text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--primary)]" x-text="files.length + '/4'"></span>
                                            <input type="file" x-ref="communityImagesInput" name="images[]" accept="image/*" multiple @change="handleImages($event)" class="hidden">
                                        </label>
                                    @endif
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold transition shadow-xs cursor-pointer select-none active:scale-[0.98]">Posting</button>
                                </div>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 text-center text-xs text-[var(--text-muted)] shadow-xs">
                        Gabung dulu ke komunitas ini untuk mulai memposting atau mengajukan pertanyaan.
                    </div>
                @endif
            @endauth

            <!-- Community feed (infinite scroll) -->
            <div x-data="infiniteFeed({
                    nextPageUrl: {{ $posts instanceof \Illuminate\Pagination\AbstractPaginator && $posts->hasMorePages() ? Illuminate\Support\Js::from(route('feeds.community', ['community' => $community->slug] + request()->query() + ['page' => $posts->currentPage() + 1])) : 'null' }},
                    lastPage: {{ $posts instanceof \Illuminate\Pagination\AbstractPaginator ? $posts->lastPage() : 1 }}
                 })">
                <div class="space-y-3" x-ref="items" data-feed-items>
                    @forelse($posts as $post)
                        <x-community-post
                            :post="$post"
                            :initial-vote="$userVotes[$post->id] ?? null"
                            :can-comment="$allowComments && ! $post->is_locked"
                            :can-moderate="$isModerator"
                            :author-role="$roleMap[$post->user_id] ?? null" />
                    @empty
                        <x-empty-state title="Belum ada postingan" description="Jadilah yang pertama memulai diskusi di komunitas ini." />
                    @endforelse
                </div>

                <div x-ref="sentinel" class="pt-4 flex justify-center">
                    <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                            class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold shadow-xs transition disabled:opacity-60 cursor-pointer">
                        <span x-show="!loading">Muat lebih banyak</span>
                        <span x-show="loading" x-cloak>Memuat...</span>
                    </button>
                </div>
            </div>
        @endif
    @endif

    {{-- Join Questions Modal --}}
    @auth
        @if(! $isMember && ! $isBanned && $community->requiresJoinApproval() && ! empty($joinQuestions))
            <div x-show="joinModalOpen" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
                 role="dialog" aria-modal="true" aria-labelledby="join-questions-title">
                <div @click.outside="joinModalOpen = false" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 id="join-questions-title" class="text-base font-bold text-[var(--primary)]">Jawab Pertanyaan Masuk</h3>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Pengurus {{ $community->name }} meninjau jawaban sebelum menyetujui keanggotaanmu.</p>
                        </div>
                        <button type="button" @click="joinModalOpen = false" class="p-1.5 rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('communities.join', $community) }}" class="space-y-3.5">
                        @csrf
                        @foreach($joinQuestions as $idx => $questionText)
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-[var(--primary)]">{{ $idx + 1 }}. {{ $questionText }}</label>
                                <textarea name="answers[{{ $idx }}]" required rows="2" maxlength="500"
                                          placeholder="Tulis jawabanmu..."
                                          class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)] resize-none"></textarea>
                            </div>
                        @endforeach

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[var(--primary)]">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="note" maxlength="500" placeholder="Perkenalkan dirimu singkat..."
                                   class="w-full px-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)]">
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="joinModalOpen = false" class="px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] text-xs font-semibold text-[var(--primary)] hover:bg-[var(--surface-hover)] cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold cursor-pointer">
                                Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

</div>
@endsection
