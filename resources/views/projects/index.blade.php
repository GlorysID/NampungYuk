@extends('layouts.app')

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')

@section('content')
<div class="space-y-6">

    <!-- 2-COLUMN WORKSPACE: MAIN FEED (XL: 8 COLS) + COMMUNITY SIDEBAR (XL: 4 COLS) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN SHOWCASE FEED (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4">

            <!-- ACTIVE FILTER BAR (Only visible when filter or search is active) -->
            @if($categorySlug || $techFilter || $search)
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-white dark:bg-[#151D1B] border border-[#DDE5E2] dark:border-[#24322F] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[#66736F] dark:text-[#8E9F9B] font-medium">Filter aktif:</span>
                        @if($categorySlug)
                            <span class="px-2 py-0.5 rounded-md bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#17211F] dark:text-[#F2F5F4] font-medium text-[11px] border border-[#DDE5E2] dark:border-[#24322F]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950/40 text-[#0F766E] dark:text-teal-300 font-mono text-[11px] border border-teal-200 dark:border-teal-800">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="px-2 py-0.5 rounded-md bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#17211F] dark:text-[#F2F5F4] text-[11px] border border-[#DDE5E2] dark:border-[#24322F]">
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

            <!-- BLOCK 2: Active Creators (Top Contributors) -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#151D1B]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#66736F] dark:text-[#8E9F9B] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0F766E] dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Top Kontributor</span>
                    </h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($topDevelopers as $dev)
                        <a href="{{ route('profile.show', $dev->username) }}" 
                           class="flex items-center justify-between p-2 rounded-lg hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <x-user-avatar :user="$dev" size="sm" />
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-[#17211F] dark:text-[#F2F5F4] group-hover:text-[#0F766E] dark:group-hover:text-teal-400 transition truncate">
                                        {{ $dev->name }}
                                    </p>
                                    <p class="text-[11px] font-mono text-[#66736F] dark:text-[#8E9F9B] truncate">&#64;{{ $dev->username }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-[#0F766E] dark:text-teal-400 px-2 py-0.5 rounded bg-teal-50 dark:bg-teal-950/40 border border-teal-200/60 dark:border-teal-800/40 shrink-0">
                                {{ $dev->reputation_points }} pt
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-[#66736F] py-2 text-center">Belum ada kontributor terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- BLOCK 3: Recent Discussions / Feedback -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#151D1B]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#66736F] dark:text-[#8E9F9B] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0F766E] dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Diskusi Terbaru</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($recentReviews as $comment)
                        <div class="p-2.5 rounded-lg bg-[#F6F8F7] dark:bg-[#0F1413] border border-[#DDE5E2]/60 dark:border-[#24322F] space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-semibold text-[#17211F] dark:text-[#F2F5F4] truncate max-w-[120px]">
                                    {{ $comment->authorName() }}
                                </span>
                                <span class="text-[#66736F] dark:text-[#8E9F9B]">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-[#66736F] dark:text-[#8E9F9B] line-clamp-2 leading-relaxed">
                                "{{ $comment->content }}"
                            </p>
                            @if($comment->project)
                                <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" 
                                   class="text-[11px] font-medium text-[#0F766E] dark:text-teal-400 hover:underline flex items-center gap-1 truncate pt-0.5">
                                    <span>di {{ $comment->project->title }}</span>
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-[#66736F] py-2 text-center">Belum ada diskusi terbaru.</p>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

</div>
@endsection
