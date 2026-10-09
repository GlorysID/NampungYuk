@extends('layouts.app')

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')

@section('content')
<div class="space-y-6" x-data="liveFeed()" x-init="init()">

    <!-- LIVE PULSE: muncul saat ada project baru (real-time via Reverb) -->
    <div x-show="newCount > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="flex justify-center">
        <button @click="revealNew()"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#0070f3] text-[#ffffff] text-xs font-bold hover:bg-[#0761d1] transition active:scale-[0.98]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#ffffff] animate-pulse"></span>
            <span x-text="newCount + ' project baru — klik untuk lihat'"></span>
        </button>
    </div>

    <!-- 2-COLUMN WORKSPACE: MAIN FEED (XL: 7 COLS) + COMMUNITY SIDEBAR (XL: 5 COLS) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN SHOWCASE FEED (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4 w-full max-w-2xl mx-auto">

            <!-- ACTIVE FILTER BAR (Only visible when filter or search is active) -->
            @if($categorySlug || $techFilter || $search)
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[#63636b] dark:text-[#a0a0a0] font-medium">Filter aktif:</span>
                        @if($categorySlug)
                            <span class="px-2 py-0.5 rounded-md bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] font-medium text-[11px] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="px-2 py-0.5 rounded-md bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-mono text-[11px] border border-[#3291ff]/30 dark:border-[#3291ff]/30">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="px-2 py-0.5 rounded-md bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] text-[11px] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                                "{{ $search }}"
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('projects.index', array_filter(['tab' => $tab])) }}" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 text-xs font-semibold shrink-0">
                        <span>Reset</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            @endif

            <!-- MAIN PROJECT FEED (infinite scroll) -->
            <div x-data="infiniteFeed({
                    nextPageUrl: {{ $projects->hasMorePages() ? Illuminate\Support\Js::from(route('feeds.projects', request()->query() + ['page' => $projects->currentPage() + 1])) : 'null' }},
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
                            description="Belum ada karya kodingan yang cocok dengan kriteria pencarian atau filter yang dipilih."
                            action-label="Kembali ke Semua Project"
                            :action-url="route('projects.index')" />
                    @endforelse
                </div>

                <!-- Infinite scroll sentinel + fallback button -->
                <div x-ref="sentinel" class="pt-4 flex justify-center">
                    <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                            class="btn-secondary text-xs py-2.5 px-5 disabled:opacity-60">
                        <span x-show="!loading">Muat lebih banyak</span>
                        <span x-show="loading" x-cloak>Memuat...</span>
                    </button>
                    <p x-show="done && {{ $projects->total() > 0 ? 'true' : 'false' }}" x-cloak class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] py-2">
                        Kamu sudah di akhir — {{ $projects->total() }} project.
                    </p>
                </div>
            </div>

        </div>

        <!-- SECONDARY SIDEBAR: COMMUNITY BLOCKS (xl:col-span-4) - FIXED/STICKY -->
        <aside class="xl:col-span-4 sticky top-20 self-start space-y-4 max-h-[calc(100vh-5.5rem)] overflow-y-auto pr-1">

            <!-- BLOCK 0: Who to follow (Social onboarding) -->
            @auth
                @if(isset($suggestedDevelopers) && $suggestedDevelopers->count() > 0)
                    <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#0a0a0a]">
                        <div class="flex items-center justify-between">
                            <h3 class="hl-label flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span>Untuk Diikuti</span>
                            </h3>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($suggestedDevelopers as $dev)
                                <div class="flex items-center justify-between gap-2">
                                    <a href="{{ route('profile.show', $dev->username) }}" class="flex items-center gap-2.5 min-w-0 group">
                                        <x-user-avatar :user="$dev" size="sm" />
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">
                                                {{ $dev->name }}
                                            </p>
                                            <p class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $dev->username }}</p>
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
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#0a0a0a]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span>Trending Hari Ini</span>
                    </h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($trendingToday as $i => $tp)
                        <a href="{{ route('projects.show', $tp->slug) }}"
                           class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
                            <span class="text-xs font-mono font-bold text-[#63636b] dark:text-[#a0a0a0] w-4 shrink-0">{{ $i + 1 }}</span>
                            <span class="w-8 h-8 rounded-lg overflow-hidden bg-[#eeeeef] dark:bg-[#171717] shrink-0">
                                @if($tp->thumbnail)
                                    <img src="{{ $tp->thumbnail }}" alt="" class="w-full h-full object-cover">
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">
                                    {{ $tp->title }}
                                </p>
                                <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $tp->user?->username ?? 'anon' }}</p>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-[#0070f3] dark:text-[#3291ff] px-2 py-0.5 rounded bg-[#e6f0ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 shrink-0 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                {{ $tp->score }}
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-[#63636b] py-2 text-center">Belum ada project trending.</p>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

</div>
@endsection
