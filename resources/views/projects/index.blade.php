@extends('layouts.app')

@php
    $isPeopleView = ($view ?? null) === 'people';
@endphp

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')
@section('header_title', $isPeopleView ? 'Direktori Developer' : 'Eksplorasi Karya')

@if(! $isPeopleView)
@section('header')
    <header class="sticky top-0 z-20 bg-[var(--bg-app)]/85 backdrop-blur-md border-b border-[var(--border-color)]">
        <!-- MOBILE TOP BAR (< lg only) -->
        <div class="lg:hidden h-14 px-4 flex items-center justify-between border-b border-[var(--border-color)]">
            <div class="flex items-center gap-2.5">
                <button @click="mobileSidebarOpen = true"
                        type="button"
                        class="p-2 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:bg-[var(--surface-hover)] hover:text-[var(--primary)] transition"
                        aria-label="Buka Menu Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <x-logo size="sm" :show-text="true" />
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button"
                        @click="mobileSearchOpen = true; $nextTick(() => document.getElementById('nav-search-mobile')?.focus())"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition"
                        aria-label="Cari">
                    <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                @auth
                    <a href="{{ route('notifications.index') }}"
                       class="relative w-9 h-9 rounded-lg flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition"
                       aria-label="Notifikasi">
                        <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>

        <!-- TABS BAR: "Untuk Anda" | "Mengikuti" | [+] (Image 1 replica) -->
        @php
            $isFollowingTab = ($tab ?? '') === 'following';
            $isForYouTab = ! $isFollowingTab;
        @endphp
        <div class="flex items-center h-[53px]">
            <!-- Tab 1: Untuk Anda -->
            <a href="{{ route('projects.index') }}"
               class="flex-1 h-full flex items-center justify-center hover:bg-white/[0.03] transition relative cursor-pointer">
                <span class="relative h-full flex items-center text-[15px] {{ $isForYouTab ? 'font-bold text-[var(--primary)]' : 'font-medium text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                    Untuk Anda
                    @if($isForYouTab)
                        <span class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500 rounded-full"></span>
                    @endif
                </span>
            </a>

            <!-- Tab 2: Mengikuti -->
            <a href="{{ route('projects.index', ['tab' => 'following']) }}"
               @guest @click.prevent="window.openAuthModal('login')" @endguest
               class="flex-1 h-full flex items-center justify-center hover:bg-white/[0.03] transition relative cursor-pointer">
                <span class="relative h-full flex items-center text-[15px] {{ $isFollowingTab ? 'font-bold text-[var(--primary)]' : 'font-medium text-[var(--text-muted)] hover:text-[var(--primary)]' }}">
                    Mengikuti
                    @if($isFollowingTab)
                        <span class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500 rounded-full"></span>
                    @endif
                </span>
            </a>

            <!-- Quick Action [+] (Image 1 replica) -->
            @auth
                <div class="px-3 shrink-0 flex items-center">
                    <a href="{{ route('projects.create') }}"
                       title="Pamer Kodingan"
                       class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </a>
                </div>
            @endauth
        </div>
    </header>
@endsection
@endif

@section('content')
<div class="space-y-5" x-data="liveFeed()" x-init="init()">

    <!-- LIVE PULSE: notification when new projects arrive in real-time -->
    <div x-show="newCount > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="flex justify-center">
        <button @click="revealNew()"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] text-xs font-bold hover:opacity-90 transition shadow-xs active:scale-[0.98]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span x-text="newCount + ' project baru — klik untuk lihat'"></span>
        </button>
    </div>

    <!-- MAIN FEED / PEOPLE VIEW -->
    <div class="space-y-5 w-full">

        @if($view === 'people')
            <!-- PEOPLE (AKUN) RESULTS -->
            <div class="space-y-3.5">
                @forelse($people as $row)
                    @php $person = $row['user']; @endphp
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 flex items-start justify-between gap-4 transition-colors">
                        <a href="{{ route('profile.show', $person->username) }}" class="shrink-0">
                            <x-user-avatar :user="$person" size="lg" />
                        </a>
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $person->username) }}" class="font-bold text-base text-[var(--primary)] hover:underline transition truncate">
                                    {{ $person->name }}
                                </a>
                            </div>
                            <p class="text-xs font-mono text-[var(--text-muted)]">&#64;{{ $person->username }}</p>
                            @if($person->bio)
                                <p class="text-sm text-[var(--text-muted)] leading-relaxed line-clamp-2">{{ $person->bio }}</p>
                            @endif
                            <div class="flex items-center gap-3 pt-1 text-xs font-mono text-[var(--text-muted)]">
                                <span><span class="font-bold text-[var(--primary)]">{{ number_format($row['followers']) }}</span> pengikut</span>
                                <span>&bull;</span>
                                <span><span class="font-bold text-[var(--primary)]">{{ number_format($row['projects']) }}</span> karya</span>
                            </div>
                        </div>
                        <div class="shrink-0 pt-0.5">
                            <x-follow-button :user="$person" size="sm" />
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        title="Akun tidak ditemukan"
                        description="{{ $search ? 'Tidak ada akun dengan nama atau username \"'.$search.'\".' : 'Belum ada akun untuk ditampilkan.' }}"
                        action-label="Kembali ke Semua Karya"
                        :action-url="route('projects.index')" />
                @endforelse

                @if($people->hasPages())
                    <div class="pt-2">{{ $people->links() }}</div>
                @endif
            </div>
        @else

            <!-- ACTIVE FILTER CHIPS BAR -->
            @if($categorySlug || $techFilter || $search || ($searchOwner ?? null))
                <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[var(--text-muted)] font-medium">Filter aktif:</span>
                        @if($searchOwner ?? null)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] text-[11px] border border-[var(--border-color)]">
                                Karya &#64;{{ $searchOwner->username }}
                            </span>
                        @endif
                        @if($categorySlug)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] text-[11px] border border-[var(--border-color)]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] font-mono text-[11px] border border-[var(--border-color)]">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] text-[11px] border border-[var(--border-color)]">
                                "{{ $search }}"
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('projects.index', array_filter(['tab' => $tab])) }}"
                       class="inline-flex items-center gap-1 text-[var(--text-muted)] hover:text-rose-600 dark:hover:text-rose-400 text-xs font-semibold shrink-0 transition-colors">
                        <span>Reset</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            @endif

            <!-- MAIN PROJECT FEED -->
            <div x-data="infiniteFeed({
                    nextPageUrl: {{ (Auth::check() && $projects->hasMorePages()) ? Illuminate\Support\Js::from(route('feeds.projects', request()->query() + ['page' => $projects->currentPage() + 1])) : 'null' }},
                    lastPage: {{ $projects->lastPage() }}
                 })">
                <div class="space-y-4" x-ref="items" data-feed-items>
                    @forelse($projects as $project)
                        @php
                            $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                            $initialVote = $userVotes[$project->id] ?? null;
                        @endphp
                        <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="in_array($project->id, $userRepostedIds ?? [])" />
                    @empty
                        <x-empty-state 
                            title="Tidak ada project ditemukan" 
                            description="{{ ($searchOwner ?? null) ? 'Belum ada karya @'.$searchOwner->username.' yang cocok dengan pencarian.' : 'Belum ada karya kodingan yang cocok dengan kriteria pencarian atau filter yang dipilih.' }}"
                            action-label="{{ ($searchOwner ?? null) ? 'Lihat Profil @'.$searchOwner->username : 'Kembali ke Semua Project' }}"
                            :action-url="($searchOwner ?? null) ? route('profile.show', $searchOwner->username) : route('projects.index')" />
                    @endforelse
                </div>

                @auth
                    <!-- Infinite scroll sentinel + fallback button for authenticated users -->
                    <div x-ref="sentinel" class="pt-6 flex justify-center">
                        <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                                class="btn-secondary text-xs py-2 px-4 disabled:opacity-60 cursor-pointer">
                            <span x-show="!loading">Muat lebih banyak</span>
                            <span x-show="loading" x-cloak>Memuat...</span>
                        </button>
                        <p x-show="done && {{ $projects->total() > 0 ? 'true' : 'false' }}" x-cloak class="text-[11px] text-zinc-500 dark:text-zinc-400 py-2">
                            Kamu sudah di akhir — {{ $projects->total() }} project.
                        </p>
                    </div>
                @else
                    <!-- GUEST DISCOVERY BANNER -->
                    <div class="mt-8 pt-6 pb-2 border-t border-[var(--border-color)]">
                        <div class="p-6 sm:p-8 rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] text-center space-y-4 shadow-xs relative overflow-hidden">
                            <div class="w-11 h-11 mx-auto rounded-xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center font-bold text-lg shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>

                            <div class="space-y-1 max-w-md mx-auto">
                                <h3 class="text-base sm:text-lg font-bold text-[var(--primary)]">Ingin memamerkan karya kodinganmu?</h3>
                                <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                                    Daftar gratis untuk mengunggah project, live demo interaktif, menyimpan koleksi bookmark, dan berdiskusi dengan developer se-Indonesia.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5 pt-2">
                                <button type="button" @click="window.openAuthModal('register')" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-zinc-950 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 font-semibold text-xs transition shadow-xs cursor-pointer">
                                    Buat Akun Gratis
                                </button>
                                <button type="button" @click="window.openAuthModal('login')" class="w-full sm:w-auto px-5 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition cursor-pointer">
                                    Masuk ke Akun
                                </button>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        @endif

    </div>

</div>
@endsection

@section('rail')
    <div class="space-y-4">

        <!-- BLOCK 0: Who to follow (Social onboarding) -->
        @auth
            @if(isset($suggestedDevelopers) && $suggestedDevelopers->count() > 0)
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                        <h3 class="text-[15px] font-bold text-[var(--primary)] flex items-center gap-2">
                            <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>Untuk Diikuti</span>
                        </h3>
                        <a href="{{ route('projects.index', ['view' => 'people']) }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lihat semua</a>
                    </div>

                    <div class="divide-y divide-[var(--border-color)]">
                        @foreach($suggestedDevelopers as $dev)
                            <div class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-[var(--surface-hover)] transition-colors">
                                <a href="{{ route('profile.show', $dev->username) }}" class="flex items-center gap-3 min-w-0 group flex-1">
                                    <x-user-avatar :user="$dev" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[13.5px] text-[var(--primary)] group-hover:underline transition truncate">
                                            {{ $dev->name }}
                                        </p>
                                        <p class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $dev->username }}</p>
                                    </div>
                                </a>
                                <x-follow-button :user="$dev" size="sm" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endauth

        <!-- BLOCK 2: Trending Projects Today -->
        <div class="bg-[var(--surface)] rounded-2xl overflow-hidden">
            <h2 class="text-xl font-extrabold text-[var(--primary)] px-4 py-3 tracking-tight">
                Trending Hari Ini
            </h2>

            <div>
                @forelse($trendingToday as $i => $tp)
                    <a href="{{ route('projects.show', $tp->slug) }}"
                       class="block px-4 py-3 hover:bg-white/[0.03] transition-colors">
                        <p class="text-[13px] text-[var(--text-muted)] truncate">
                            Trending &middot; #{{ $i + 1 }}
                        </p>
                        <p class="text-[15px] font-bold text-[var(--primary)] leading-snug truncate mt-0.5">
                            {{ $tp->title }}
                        </p>
                        <p class="text-[13px] text-[var(--text-muted)] truncate mt-1">
                            <span class="font-mono">&#64;{{ $tp->user?->username ?? 'anon' }}</span>
                            &middot;
                            <svg class="inline-block w-3 h-3 align-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                            </svg>
                            <span class="font-mono">{{ number_format($tp->score) }}</span> upvote
                        </p>
                    </a>
                @empty
                    <p class="px-4 py-6 text-center text-xs text-[var(--text-muted)]">Belum ada project trending hari ini.</p>
                @endforelse
            </div>

            <a href="{{ route('projects.index', ['tab' => 'trend']) }}"
               class="block px-4 py-3.5 text-sm font-normal text-emerald-400 hover:bg-white/[0.03] transition-colors">
                Tampilkan lebih banyak
            </a>
        </div>

        <!-- BLOCK: Sedang Hangat Dibicarakan (most commented) -->
        <div class="bg-[var(--surface)] rounded-2xl overflow-hidden">
            <h2 class="text-xl font-extrabold text-[var(--primary)] px-4 py-3 tracking-tight">
                Sedang Hangat Dibicarakan
            </h2>

            <div>
                @forelse($hotHashtags as $tag => $count)
                    <a href="{{ route('projects.index', ['tech' => $tag]) }}"
                       title="#{{ $tag }} ({{ $count }} karya)"
                       class="block px-4 py-3 hover:bg-white/[0.03] transition-colors">
                        <p class="text-[13px] text-[var(--text-muted)] truncate">
                            Topik &middot; #{{ $loop->iteration }}
                        </p>
                        <p class="text-[15px] font-bold text-[var(--primary)] leading-snug truncate mt-0.5">
                            #{{ $tag }}
                        </p>
                        <p class="text-[13px] text-[var(--text-muted)] truncate mt-1">
                            <span class="font-mono">{{ number_format($count) }}</span> karya
                        </p>
                    </a>
                @empty
                    <p class="px-4 py-6 text-center text-xs text-[var(--text-muted)]">Belum ada hashtag hangat.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection
