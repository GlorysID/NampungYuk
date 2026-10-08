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
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#0e9c8b] text-[#04110f] text-xs font-bold hover:bg-[#0b7d70] transition active:scale-[0.98]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#04110f] animate-pulse"></span>
            <span x-text="newCount + ' project baru — klik untuk lihat'"></span>
        </button>
    </div>

    <!-- 2-COLUMN WORKSPACE: MAIN FEED (XL: 7 COLS) + COMMUNITY SIDEBAR (XL: 5 COLS) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN SHOWCASE FEED (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4 w-full max-w-2xl mx-auto">

            <!-- ACTIVE FILTER BAR (Only visible when filter or search is active) -->
            @if($categorySlug || $techFilter || $search)
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[#5c6979] dark:text-[#7e8a9a] font-medium">Filter aktif:</span>
                        @if($categorySlug)
                            <span class="px-2 py-0.5 rounded-md bg-[#e6eaee] dark:bg-[#1e2530] text-[#10161f] dark:text-[#eaecf0] font-medium text-[11px] border border-[#d5dbe2] dark:border-[#262d3a]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="px-2 py-0.5 rounded-md bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] font-mono text-[11px] border border-[#50d2c1]/30 dark:border-[#50d2c1]/30">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="px-2 py-0.5 rounded-md bg-[#e6eaee] dark:bg-[#1e2530] text-[#10161f] dark:text-[#eaecf0] text-[11px] border border-[#d5dbe2] dark:border-[#262d3a]">
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

            <!-- MAIN PROJECT FEED -->
            <div class="space-y-4">
                @forelse($projects as $project)
                    @php
                        $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                        $initialVote = $userVotes[$project->id] ?? null;
                    @endphp
                    
                    <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" />
                @empty
                    <x-empty-state 
                        title="Tidak ada project ditemukan" 
                        description="Belum ada karya kodingan yang cocok dengan kriteria pencarian atau filter yang dipilih."
                        action-label="Kembali ke Semua Project"
                        :action-url="route('projects.index')" />
                @endforelse
            </div>

            <!-- PAGINATION -->
            @if($projects->hasPages())
                <div class="pt-4">
                    {{ $projects->links() }}
                </div>
            @endif

        </div>

        <!-- SECONDARY SIDEBAR: COMMUNITY BLOCKS (xl:col-span-4) - FIXED/STICKY -->
        <aside class="xl:col-span-4 sticky top-20 self-start space-y-4 max-h-[calc(100vh-5.5rem)] overflow-y-auto pr-1">

            <!-- BLOCK 0: Who to follow (Social onboarding) -->
            @auth
                @if(isset($suggestedDevelopers) && $suggestedDevelopers->count() > 0)
                    <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#141821]">
                        <div class="flex items-center justify-between">
                            <h3 class="hl-label flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#0e9c8b] dark:text-[#50d2c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            <p class="font-bold text-xs text-[#10161f] dark:text-[#eaecf0] group-hover:text-[#0e9c8b] dark:group-hover:text-[#50d2c1] transition truncate">
                                                {{ $dev->name }}
                                            </p>
                                            <p class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a] truncate">&#64;{{ $dev->username }}</p>
                                        </div>
                                    </a>
                                    <x-follow-button :user="$dev" size="sm" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            <!-- BLOCK 2: Active Creators (Top Contributors) -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#141821]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0e9c8b] dark:text-[#50d2c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Top Kontributor</span>
                    </h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($topDevelopers as $dev)
                        <a href="{{ route('profile.show', $dev->username) }}" 
                           class="flex items-center justify-between p-2 rounded-lg hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c] transition group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <x-user-avatar :user="$dev" size="sm" />
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-[#10161f] dark:text-[#eaecf0] group-hover:text-[#0e9c8b] dark:group-hover:text-[#50d2c1] transition truncate">
                                        {{ $dev->name }}
                                    </p>
                                    <p class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a] truncate">&#64;{{ $dev->username }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-[#0e9c8b] dark:text-[#50d2c1] px-2 py-0.5 rounded bg-[#d9fbf4] dark:bg-[#50d2c1]/12 border border-[#50d2c1]/30 dark:border-[#50d2c1]/30 shrink-0">
                                {{ $dev->reputation_points }} pt
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-[#5c6979] py-2 text-center">Belum ada kontributor terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- BLOCK 3: Recent Discussions / Feedback -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#141821]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0e9c8b] dark:text-[#50d2c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Diskusi Terbaru</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($recentReviews as $comment)
                        <div class="p-2.5 rounded-lg bg-[#eceff1] dark:bg-[#0b0e11] border border-[#d5dbe2]/60 dark:border-[#262d3a] space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-semibold text-[#10161f] dark:text-[#eaecf0] truncate max-w-[120px]">
                                    {{ $comment->authorName() }}
                                </span>
                                <span class="text-[#5c6979] dark:text-[#7e8a9a]">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a] line-clamp-2 leading-relaxed">
                                "{{ $comment->content }}"
                            </p>
                            @if($comment->project)
                                <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" 
                                   class="text-[11px] font-medium text-[#0e9c8b] dark:text-[#50d2c1] hover:underline flex items-center gap-1 truncate pt-0.5">
                                    <span>di {{ $comment->project->title }}</span>
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-[#5c6979] py-2 text-center">Belum ada diskusi terbaru.</p>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

</div>
@endsection
