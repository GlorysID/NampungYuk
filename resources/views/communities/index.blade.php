@extends('layouts.app')

@section('title', 'Komunitas Developer — NampungYuk')
@section('header_title', 'Komunitas Developer')
@section('meta_description', 'Temukan komunitas developer sesuai minatmu. Berbagi ilmu, tanya jawab coding, dan kolaborasi project.')

@section('content')
<div class="space-y-6" x-data>

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] text-xs font-semibold mb-1.5">
                <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Forum & Diskusi Developer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--primary)] tracking-tight">Komunitas</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Tanya jawab coding, diskusikan tantangan arsitektur, dan terhubung dengan sesama developer.</p>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            @auth
                <button type="button" @click="$refs.createModal.showModal()" class="px-4 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold inline-flex items-center gap-2 cursor-pointer transition shadow-xs select-none active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Komunitas</span>
                </button>
            @else
                <button type="button" @click="window.openAuthModal('login')" class="px-4 py-2.5 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-sm font-bold inline-flex items-center gap-2 cursor-pointer transition shadow-xs select-none active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Komunitas</span>
                </button>
            @endauth
        </div>
    </div>

    <!-- MANAGED COMMUNITIES (Owner, Admin, Moderator) -->
    @auth
        @if(($managedCommunities ?? collect())->count() > 0)
            <section class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 sm:p-5 space-y-3 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-mono font-semibold uppercase tracking-[0.1em] text-[var(--text-faint)]">Pengelolaan Grup</p>
                        <h2 class="text-sm sm:text-base font-bold text-[var(--primary)] mt-0.5">Komunitas yang Kamu Kelola</h2>
                    </div>
                    <span class="text-xs font-mono text-[var(--text-muted)]">{{ $managedCommunities->count() }} grup</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($managedCommunities as $mc)
                        @php
                            $mcRole = $managedRoles[$mc->id] ?? 'mod';
                            $mcRoleLabel = \App\Models\CommunityMember::labelForRole($mcRole);
                            $mcPending = (int) ($mc->pending_join_requests_count ?? 0) + (int) ($mc->pending_posts_count ?? 0);
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                            @if($mc->icon)
                                <img src="{{ $mc->icon }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0 border border-[var(--border-color)]">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center font-bold text-sm shrink-0">{{ mb_substr($mc->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('communities.show', $mc->slug) }}" class="text-sm font-bold text-[var(--primary)] hover:underline truncate block">{{ $mc->name }}</a>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full bg-[var(--surface)] border border-[var(--border-color)] text-[var(--text-muted)]">{{ $mcRoleLabel }}</span>
                                    @if($mcPending > 0)
                                        <span class="text-[10.5px] font-mono font-bold px-2 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400">{{ $mcPending }} antrian</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('communities.manage.index', $mc->slug) }}"
                               class="px-3 py-1.5 rounded-lg bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] hover:bg-[var(--surface-hover)] text-xs font-semibold text-[var(--primary)] transition shrink-0">
                                Kelola
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth

    <!-- VIEW MODE SWITCHER & FILTER TABS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-1 border-b border-[var(--border-color)]">
        
        <!-- View Toggle: Linimasa Diskusi vs Katalog Komunitas -->
        <div class="inline-flex items-center p-1 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] text-sm">
            <a href="{{ route('communities.index', array_filter(['view' => 'feed', 'tab' => $tab, 'q' => $search])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2 {{ $view !== 'direktori' ? 'bg-[var(--surface)] text-[var(--primary)] shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Linimasa Diskusi</span>
            </a>
            <a href="{{ route('communities.index', array_filter(['view' => 'direktori', 'tab' => $tab, 'q' => $search])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2 {{ $view === 'direktori' ? 'bg-[var(--surface)] text-[var(--primary)] shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Direktori Komunitas</span>
            </a>
        </div>

        <!-- Pill filter tabs: Untukmu, Diikuti, Populer, Baru -->
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $communityTabs = ['untukmu' => 'Untukmu'];
                if (auth()->check()) {
                    $communityTabs['diikuti'] = 'Diikuti';
                }
                $communityTabs['populer'] = 'Populer';
                $communityTabs['baru'] = 'Baru';
            @endphp
            @foreach($communityTabs as $key => $label)
                <a href="{{ route('communities.index', array_filter(['view' => $view, 'tab' => $key, 'q' => $search])) }}"
                   class="shrink-0 inline-flex items-center px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-medium transition {{ $tab === $key ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="space-y-4">

        <!-- Search box (visible below xl; xl+ uses contextual right-rail #nav-search) -->
        <form method="GET" action="{{ route('communities.index') }}" class="relative w-full xl:hidden">
            @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
            @if($view) <input type="hidden" name="view" value="{{ $view }}"> @endif
            <svg class="w-4 h-4 text-[#908F88] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $search }}" aria-label="Cari komunitas atau diskusi" placeholder="{{ $view === 'direktori' ? 'Cari nama komunitas atau topik...' : 'Cari pertanyaan atau topik diskusi developer...' }}" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-xs sm:text-sm outline-none focus:border-[var(--primary)] shadow-xs transition">
        </form>

        <!-- SPACES SHORTCUT BANNER -->
        <a href="{{ route('spaces.index') }}" class="group bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs transition-colors">
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Spaces Live</span>
                    </span>
                    <span class="text-[11px] text-[var(--text-muted)]">Audio & Video Rooms</span>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-[var(--primary)] tracking-tight group-hover:text-[var(--primary-hover)] transition">
                    Diskusi Live di Spaces
                </h3>
                <p class="text-xs text-[var(--text-muted)]">
                    Buat atau gabung ruang ngobrol audio/video bareng developer lain secara langsung.
                </p>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-[var(--primary)] group-hover:translate-x-0.5 transition-transform shrink-0">
                <span>Buka Spaces</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>

        <!-- VIEW 1: LINIMASA DISKUSI -->
        @if($view !== 'direktori')
            <div class="space-y-4">
                
                @forelse($posts as $post)
                    <x-community-post 
                        :post="$post" 
                        :initial-vote="$userVotes[$post->id] ?? null" 
                        :can-comment="true" 
                        :can-moderate="false" 
                        :show-community="true" />
                @empty
                    <x-empty-state 
                        title="Belum ada diskusi di kategori ini" 
                        description="Jadilah yang pertama membuka obrolan atau pertanyaan di komunitas developer!"
                        action-label="Lihat Direktori Komunitas"
                        :action-url="route('communities.index', ['view' => 'direktori'])" />
                @endforelse

                @if($posts->hasPages())
                    <div class="pt-4">{{ $posts->links() }}</div>
                @endif

                @guest
                    <!-- Soft Auth Prompt inside Feed -->
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 text-center space-y-3 shadow-xs">
                        <h4 class="text-sm font-bold text-[var(--primary)]">Ingin ikut berdiskusi atau bertanya ke sesama developer?</h4>
                        <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto">
                            Masuk ke akun NampungYuk untuk membuat postingan, memberikan upvote, atau bergabung ke komunitas favoritmu.
                        </p>
                        <div class="pt-1 flex items-center justify-center gap-2">
                            <button type="button" @click="window.openAuthModal('login')" class="px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold transition cursor-pointer">
                                Masuk ke Akun
                            </button>
                            <button type="button" @click="window.openAuthModal('register')" class="px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold transition shadow-xs cursor-pointer">
                                Daftar Gratis
                            </button>
                        </div>
                    </div>
                @endguest

            </div>

        <!-- VIEW 2: DIREKTORI KATALOG KOMUNITAS -->
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($communities as $community)
                    @php $joined = in_array($community->id, $joinedIds); @endphp
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-4 flex flex-col gap-3 shadow-xs transition-colors">
                        <div class="flex items-start gap-3">
                            @if($community->icon)
                                <img src="{{ $community->icon }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-[var(--border-color)]">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center font-bold text-lg shrink-0">{{ mb_substr($community->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('communities.show', $community->slug) }}" class="font-bold text-sm text-[var(--primary)] hover:text-[var(--primary-hover)] transition truncate block">{{ $community->name }}</a>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($community->topic)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">{{ $community->topic }}</span>
                                    @endif
                                    @if($community->isPrivate())
                                        <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400">Private</span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400">Publik</span>
                                    @endif
                                    @if($community->requiresJoinApproval() && ! $community->isPrivate())
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">Persetujuan</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-[var(--text-muted)] leading-relaxed line-clamp-3 min-h-[3rem]">
                            {{ $community->description ?: 'Belum ada deskripsi komunitas.' }}
                        </p>

                        <div class="flex items-center gap-3 text-[11px] text-[var(--text-muted)] font-mono">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ number_format($community->members_count) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ number_format($community->posts_count) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mt-auto">
                            @auth
                                @if(! $joined && $community->requiresJoinApproval() && ! empty($community->join_questions))
                                    <a href="{{ route('communities.show', $community->slug) }}" class="flex-1 text-center text-xs font-semibold py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] transition shadow-xs select-none">
                                        Ajukan Gabung
                                    </a>
                                @else
                                    <button type="button" x-data="communityJoin({ joined: {{ $joined ? 'true' : 'false' }}, pending: false, url: '{{ route('communities.join', $community) }}' })"
                                            @click="toggle()" :disabled="loading"
                                            :class="joined ? 'bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]' : (pending ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)]')"
                                            class="flex-1 text-xs font-semibold py-2 rounded-xl transition disabled:opacity-60 cursor-pointer shadow-xs select-none active:scale-[0.98]"
                                            x-text="joined ? 'Bergabung' : (pending ? 'Menunggu' : '{{ $community->requiresJoinApproval() ? 'Minta Gabung' : 'Gabung' }}')"></button>
                                @endif
                            @else
                                <button type="button" @click="window.openAuthModal('login')" class="flex-1 text-xs font-semibold py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] transition cursor-pointer shadow-xs select-none active:scale-[0.98]">
                                    Gabung
                                </button>
                            @endauth
                            <a href="{{ route('communities.show', $community->slug) }}" class="text-xs font-semibold py-2 px-3 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] transition {{ auth()->check() ?'' :'flex-1 text-center' }}">Lihat Komunitas</a>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2">
                        <x-empty-state title="Belum ada komunitas" description="Jadilah yang pertama membuat komunitas developer!" />
                    </div>
                @endforelse
            </div>

            @if($communities->hasPages())
                <div class="pt-4">{{ $communities->links() }}</div>
            @endif
        @endif

    </div>

    <!-- Create Community Modal -->
    @auth
        @php
            $followersCount = (int) ($followersCount ?? 0);
            $minFollowersToCreate = (int) ($minFollowersToCreate ?? \App\Models\Community::MIN_FOLLOWERS_TO_CREATE);
            $canCreateCommunity = (bool) ($canCreateCommunity ?? false);
            $followerProgress = $minFollowersToCreate > 0 ? min(100, (int) round(($followersCount / $minFollowersToCreate) * 100)) : 100;
            $remainingFollowers = max(0, $minFollowersToCreate - $followersCount);
        @endphp
        <dialog x-ref="createModal" class="rounded-2xl p-0 w-full max-w-md backdrop:bg-black/60 bg-transparent">
            @if(! $canCreateCommunity)
                <div class="bg-[var(--surface)] p-6 space-y-4 text-left border border-[var(--border-color)] rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-[var(--primary)]">Buat Komunitas Baru</h3>
                        <button type="button" @click="$refs.createModal.close()" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] p-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-xl bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-[var(--primary)] shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-[var(--primary)]">Minimal {{ number_format($minFollowersToCreate) }} pengikut diperlukan</p>
                                <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                                    Untuk menjaga kualitas forum, hanya kreator dengan minimal {{ number_format($minFollowersToCreate) }} akun pengikut yang dapat membuat komunitas baru.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-[11px] font-mono">
                                <span class="text-[var(--primary)] font-semibold">{{ number_format($followersCount) }} / {{ number_format($minFollowersToCreate) }} pengikut</span>
                                <span class="text-[var(--text-muted)]">Kurang {{ number_format($remainingFollowers) }} lagi</span>
                            </div>
                            <div class="h-2 rounded-full bg-[var(--surface-muted)] overflow-hidden border border-[var(--border-color)]">
                                <div class="h-full bg-[var(--primary)] transition-all" style="width: {{ $followerProgress }}%"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-[var(--text-muted)] leading-relaxed">
                        Bagikan karya terbaikmu dan aktif berdiskusi agar lebih banyak developer mengikuti akunmu.
                    </p>

                    <div class="flex justify-end pt-1">
                        <button type="button" @click="$refs.createModal.close()" class="px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold transition shadow-xs cursor-pointer">Mengerti</button>
                    </div>
                </div>
            @else
            <form method="POST" action="{{ route('communities.store') }}" class="bg-[var(--surface)] p-6 space-y-4 text-left border border-[var(--border-color)] rounded-2xl shadow-xl">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-[var(--primary)]">Buat Komunitas Baru</h3>
                    <button type="button" @click="$refs.createModal.close()" class="text-[#908F88] hover:text-[var(--primary)] transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @if(auth()->user()?->isAdmin() && $followersCount < $minFollowersToCreate)
                    <p class="text-[11px] font-mono text-[var(--text-muted)] bg-[var(--surface-subtle)] border border-[var(--border-color)] rounded-xl px-3 py-2">
                        Akses Admin aktif — bebas syarat minimal {{ number_format($minFollowersToCreate) }} pengikut.
                    </p>
                @endif
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Nama Komunitas</label>
                    <input type="text" name="name" required maxlength="60" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)] transition" placeholder="mis. Web Dev Indonesia">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Topik</label>
                    <input type="text" name="topic" maxlength="60" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)] transition" placeholder="mis. Programming, AI, Mobile">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Deskripsi</label>
                    <textarea name="description" rows="3" maxlength="500" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)] resize-none transition" placeholder="Komunitas ini dibuat untuk..."></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-[var(--primary)]">Visibilitas</label>
                        <select name="visibility" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] text-sm outline-none focus:border-[var(--primary)] transition">
                            <option value="public">Publik</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-[var(--primary)]">Izin Bergabung</label>
                        <select name="join_policy" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] text-sm outline-none focus:border-[var(--primary)] transition">
                            <option value="open">Langsung Gabung</option>
                            <option value="approval">Persetujuan Admin</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Izin Memposting</label>
                    <select name="post_policy" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] text-sm outline-none focus:border-[var(--primary)] transition">
                        <option value="all_members">Semua Anggota</option>
                        <option value="approval_required">Perlu Persetujuan Admin/Mod</option>
                        <option value="admins_only">Hanya Admin &amp; Moderator</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[var(--primary)]">Pesan Sambutan (Opsional)</label>
                    <input type="text" name="welcome_message" maxlength="500" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-sm outline-none focus:border-[var(--primary)] transition" placeholder="Selamat datang di komunitas kami...">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="$refs.createModal.close()" class="px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold transition shadow-xs cursor-pointer">Buat Komunitas</button>
                </div>
            </form>
            @endif
        </dialog>
    @endauth

</div>
@endsection

@section('rail')
    <div class="space-y-4">

        <!-- Top Communities List -->
        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 space-y-3 shadow-xs">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Top Komunitas</span>
                </h3>
                <a href="{{ route('communities.index', ['view' => 'direktori']) }}" class="text-[11px] font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="space-y-1.5">
                @foreach($topCommunities as $i => $c)
                    <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-[var(--surface-subtle)] transition group">
                        <span class="text-xs font-mono font-bold text-[var(--text-faint)] w-4 shrink-0">{{ $i + 1 }}</span>
                        @if($c->icon)
                            <img src="{{ $c->icon }}" alt="" class="w-8 h-8 rounded-lg object-cover shrink-0 border border-[var(--border-color)]">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-xs text-[var(--primary)] group-hover:text-[var(--primary-hover)] transition truncate">{{ $c->name }}</p>
                            <p class="text-[10px] text-[var(--text-muted)]">{{ number_format($c->members_count) }} anggota</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Followed Communities (If logged in) -->
        @auth
            @if($followedCommunities->count() > 0)
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 space-y-3 shadow-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Komunitas yang Diikuti</span>
                    </h3>
                    <div class="space-y-1.5">
                        @foreach($followedCommunities as $c)
                            <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-[var(--surface-subtle)] transition group">
                                @if($c->icon)
                                    <img src="{{ $c->icon }}" alt="" class="w-7 h-7 rounded-lg object-cover shrink-0">
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                                @endif
                                <p class="font-semibold text-xs text-[var(--primary)] group-hover:text-[var(--primary-hover)] transition truncate flex-1">{{ $c->name }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endauth

        <!-- Information Guide Card -->
        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 text-xs space-y-2 text-[var(--text-muted)] shadow-xs">
            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--primary)] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <span>Tips Komunitas</span>
            </h4>
            <p class="text-[11px] leading-relaxed">
                Setiap komunitas memiliki ruang diskusi dan topik khusus. Ajukan pertanyaan seputar bug, minta review kode, atau mulai diskusi topik baru bersama developer lain.
            </p>
        </div>

    </div>
@endsection
