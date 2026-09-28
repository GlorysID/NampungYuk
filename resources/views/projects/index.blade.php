@extends('layouts.app')

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')

@section('content')
<div class="space-y-6">

    <!-- 2-COLUMN WORKSPACE: MAIN FEED (XL: 8 COLS) + COMMUNITY SIDEBAR (XL: 4 COLS) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN SHOWCASE FEED (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4">

            <!-- FILTER & NAVIGATION BAR -->
            <div class="ny-card p-3 sm:p-4 space-y-3 bg-white dark:bg-[#151D1B]">
                
                <!-- Top Row: Sort Tabs & Active Filter Badges -->
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    
                    <!-- Segmented Tabs (Trending, Terbaru, Populer, Prototipe) -->
                    <div class="inline-flex p-1 rounded-lg bg-[#EBF0EE] dark:bg-[#1F2C29] text-xs font-semibold select-none border border-[#DDE5E2] dark:border-[#24322F]">
                        <!-- Trending -->
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'trend'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md transition {{ $tab === 'trend' ? 'bg-white dark:bg-[#151D1B] text-[#0F766E] dark:text-teal-300 shadow-2xs font-bold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'trend' ? 'text-[#0F766E] dark:text-teal-400' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                            </svg>
                            <span>Trending</span>
                        </a>

                        <!-- Terbaru -->
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'terbaru'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md transition {{ $tab === 'terbaru' ? 'bg-white dark:bg-[#151D1B] text-[#0F766E] dark:text-teal-300 shadow-2xs font-bold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'terbaru' ? 'text-[#0F766E] dark:text-teal-400' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Terbaru</span>
                        </a>

                        <!-- Populer -->
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'populer'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md transition {{ $tab === 'populer' ? 'bg-white dark:bg-[#151D1B] text-[#0F766E] dark:text-teal-300 shadow-2xs font-bold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'populer' ? 'text-[#0F766E] dark:text-teal-400' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span>Populer</span>
                        </a>

                        <!-- Prototipe Interaktif -->
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'prototype'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md transition {{ $tab === 'prototype' ? 'bg-white dark:bg-[#151D1B] text-purple-700 dark:text-purple-300 shadow-2xs font-bold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-purple-700 dark:hover:text-purple-300' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'prototype' ? 'text-purple-600' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                            <span>Prototipe</span>
                        </a>
                    </div>

                    <!-- Active Filter Tags & Reset -->
                    @if($categorySlug || $techFilter || $search)
                        <div class="flex items-center gap-2 text-xs flex-wrap">
                            <span class="text-[#66736F] dark:text-[#8E9F9B] text-xs">Filter aktif:</span>
                            @if($categorySlug)
                                <span class="px-2 py-0.5 rounded-md bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#17211F] dark:text-[#F2F5F4] font-mono text-[11px] border border-[#DDE5E2] dark:border-[#24322F]">
                                    {{ $categorySlug }}
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
                            <a href="{{ route('projects.index', ['tab' => $tab]) }}" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 text-xs ml-1 font-medium">
                                <span>Reset</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Bottom Row: Category Filter Chips -->
                @if(isset($categories) && count($categories) > 0)
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar pt-2 border-t border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                        @php $isAllActive = !$categorySlug; @endphp
                        <a href="{{ route('projects.index', array_merge(request()->except('kategori'), ['tab' => $tab])) }}" 
                           class="group shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $isAllActive ? 'bg-[#0F766E] text-white shadow-2xs font-semibold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] border border-[#DDE5E2] dark:border-[#24322F]' }}">
                            <span>Semua Kategori</span>
                        </a>
                        @foreach($categories as $cat)
                            @php $isActive = ($categorySlug === $cat->slug); @endphp
                            <a href="{{ route('projects.index', array_merge(request()->except('kategori'), ['tab' => $tab, 'kategori' => $cat->slug])) }}" 
                               class="group shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $isActive ? 'bg-[#0F766E] text-white shadow-2xs font-semibold' : 'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] border border-[#DDE5E2] dark:border-[#24322F]' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#66736F]' }}">
                                    {{ $cat->projects_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

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

        <!-- SECONDARY SIDEBAR: 3 VALUABLE COMMUNITY BLOCKS (xl:col-span-4) -->
        <aside class="xl:col-span-4 space-y-4">

            <!-- BLOCK 1: Trending Tech Stacks -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#151D1B]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#66736F] dark:text-[#8E9F9B] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0F766E] dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Tech Stack Populer</span>
                    </h3>
                </div>

                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($trendingTech as $tech)
                        <x-tech-pill :name="$tech" />
                    @endforeach
                </div>
            </div>

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
