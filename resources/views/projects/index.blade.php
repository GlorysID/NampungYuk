@extends('layouts.app')

@section('title', 'NampungYuk — Platform Showcase Project Programmer')

@section('content')
<div class="space-y-6"
     x-data="{
         inspectDrawer: {
             open: false,
             id: null,
             title: '',
             tagline: '',
             description: '',
             authorName: '',
             authorUsername: '',
             authorAvatar: '',
             categoryName: '',
             techs: [],
             score: 0,
             demoUrl: '',
             githubUrl: '',
             prototypeUrl: '',
             slug: '',
             installCmd: ''
         },
         openInspect(data) {
             this.inspectDrawer = { open: true, ...data };
         },
         closeInspect() {
             this.inspectDrawer.open = false;
         },
         copyText(text, msg) {
             navigator.clipboard.writeText(text);
             notify(msg || 'Berhasil disalin ke clipboard!');
         }
     }">

    <!-- 2-COLUMN PRODUCTIVE LAYOUT: MAIN FEED (8 COLS) + ACTIVE COMMUNITY SIDEBAR (4 COLS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: MAIN SHOWCASE FEED (lg:col-span-8) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- SEGMENTED FILTER & STACK NAVIGATION (SUPABASE STYLE) -->
            <div class="spotlight-card p-3 sm:p-4 space-y-3 bg-white dark:bg-[#0a0a0c] border border-neutral-200/80 dark:border-white/[0.08]">
                <!-- Top Row: Sort Segmented Tabs & Filter Reset -->
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="inline-flex p-1 rounded-xl bg-neutral-100 dark:bg-[#141417] border border-neutral-200 dark:border-white/[0.06] text-xs font-semibold">
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'trend'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $tab === 'trend' ? 'bg-white dark:bg-[#202024] text-neutral-900 dark:text-white shadow-2xs font-bold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'trend' ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                            </svg>
                            <span>Trending Builds</span>
                        </a>
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'terbaru'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $tab === 'terbaru' ? 'bg-white dark:bg-[#202024] text-neutral-900 dark:text-white shadow-2xs font-bold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'terbaru' ? 'text-amber-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Fresh Deploy</span>
                        </a>
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'populer'])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $tab === 'populer' ? 'bg-white dark:bg-[#202024] text-neutral-900 dark:text-white shadow-2xs font-bold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5 {{ $tab === 'populer' ? 'text-yellow-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span>Hall of Fame</span>
                        </a>
                    </div>

                    @if($categorySlug || $techFilter || $search)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-neutral-400 font-mono text-[11px]">Filter:</span>
                            @if($categorySlug)
                                <span class="px-2 py-0.5 rounded-md bg-neutral-200 dark:bg-white/10 text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
                                    {{ $categorySlug }}
                                </span>
                            @endif
                            @if($techFilter)
                                <span class="px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-500 font-mono text-[11px] border border-orange-500/20">
                                    #{{ $techFilter }}
                                </span>
                            @endif
                            @if($search)
                                <span class="px-2 py-0.5 rounded-md bg-neutral-200 dark:bg-white/10 text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
                                    "{{ $search }}"
                                </span>
                            @endif
                            <a href="{{ route('projects.index', ['tab' => $tab]) }}" class="inline-flex items-center gap-1 text-rose-500 hover:underline font-mono text-xs ml-1">
                                <span>Reset</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Bottom Row: Sleek Modern Category Filter Chips -->
                @if(isset($categories) && count($categories) > 0)
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar pt-2 border-t border-neutral-100 dark:border-white/[0.06]">
                        @php $isAllActive = !$categorySlug; @endphp
                        <a href="{{ route('projects.index', array_merge(request()->except('kategori'), ['tab' => $tab])) }}" 
                           class="group shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $isAllActive ? 'bg-neutral-900 text-white dark:bg-[#1f1f23] dark:text-white border border-neutral-300 dark:border-white/[0.14] shadow-2xs font-semibold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04] border border-transparent' }}">
                            @if($isAllActive)
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0"></span>
                            @endif
                            <span>Semua Kategori</span>
                        </a>
                        @foreach($categories as $cat)
                            @php $isActive = ($categorySlug === $cat->slug); @endphp
                            <a href="{{ route('projects.index', array_merge(request()->except('kategori'), ['tab' => $tab, 'kategori' => $cat->slug])) }}" 
                               class="group shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $isActive ? 'bg-neutral-900 text-white dark:bg-[#1f1f23] dark:text-white border border-neutral-300 dark:border-white/[0.14] shadow-2xs font-semibold' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04] border border-transparent' }}">
                                @if($isActive)
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0"></span>
                                @endif
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-md transition-colors {{ $isActive ? 'bg-neutral-800 dark:bg-white/10 text-neutral-300 dark:text-neutral-200' : 'bg-neutral-200/60 dark:bg-white/[0.05] text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-300' }}">
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
                        $initialUserVote = $userVotes[$project->id] ?? null;
                    @endphp
                    
                    <article class="project-nav-card spotlight-card p-5 sm:p-6 space-y-4 group transition duration-200 border border-neutral-200/80 dark:border-white/[0.08] {{ $project->prototype_url ? 'ring-1 ring-purple-500/25 shadow-[0_0_24px_-4px_rgba(168,85,247,0.12)]' : '' }} bg-white dark:bg-[#09090b] relative overflow-hidden"
                             x-data="{
                                 cardTab: 'preview',
                                 score: {{ $project->score }},
                                 userVote: '{{ $initialUserVote }}',
                                 isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
                                 isVoting: false,
                                 isBookmarking: false,
                                 async vote(type) {
                                     if (this.isVoting) return;
                                     this.isVoting = true;
                                     try {
                                         const res = await fetch('{{ route('projects.vote', $project) }}', {
                                             method: 'POST',
                                             headers: {
                                                 'Content-Type': 'application/json',
                                                 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                 'Accept': 'application/json'
                                             },
                                             body: JSON.stringify({ type })
                                         });
                                         if (res.status === 401) {
                                             window.location.href = '{{ route('login') }}';
                                             return;
                                         }
                                         const data = await res.json();
                                         if (data.success) {
                                             this.score = data.score;
                                             this.userVote = data.user_vote;
                                             notify(type === 'up' ? 'Upvoted! Terima kasih atas apresiasinya.' : 'Vote tercatat.');
                                         }
                                     } catch(e) {
                                         notify('Gagal memberikan vote');
                                     } finally {
                                         this.isVoting = false;
                                     }
                                 },
                                 async toggleBookmark() {
                                     if (this.isBookmarking) return;
                                     this.isBookmarking = true;
                                     try {
                                         const res = await fetch('{{ route('projects.bookmark', $project) }}', {
                                             method: 'POST',
                                             headers: {
                                                 'Content-Type': 'application/json',
                                                 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                 'Accept': 'application/json'
                                             }
                                         });
                                         if (res.status === 401) {
                                             window.location.href = '{{ route('login') }}';
                                             return;
                                         }
                                         const data = await res.json();
                                         if (data.success) {
                                             this.isBookmarked = data.bookmarked;
                                             notify(this.isBookmarked ? 'Proyek disimpan ke Koleksi!' : 'Dihapus dari Koleksi.');
                                         }
                                     } catch(e) {
                                         notify('Gagal menyimpan bookmark');
                                     } finally {
                                         this.isBookmarking = false;
                                     }
                                 },
                                 copyLink() {
                                     navigator.clipboard.writeText('{{ route('projects.show', $project->slug) }}');
                                     notify('Tautan project berhasil disalin!');
                                 }
                             }">

                        @if($project->prototype_url)
                            <!-- Distinct Top Gradient Line for Prototype Projects -->
                            <div class="absolute top-0 left-0 right-0 h-[2.5px] bg-gradient-to-r from-purple-500 via-indigo-500 to-pink-500 z-10"></div>
                        @endif

                        <!-- Header Row: Author + Live Status + Card View Switcher -->
                        <div class="flex items-center justify-between gap-3 text-xs flex-wrap">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($project->user)
                                    <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                                        <img src="{{ $project->user->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($project->user->name) }}" 
                                             alt="{{ $project->user->name }}" 
                                             class="w-8 h-8 rounded-xl bg-neutral-100 dark:bg-[#1a1a1e] object-cover ring-1 ring-neutral-200 dark:ring-white/10 shadow-2xs">
                                    </a>
                                    <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-neutral-900 dark:text-white hover:text-orange-500 transition">
                                            {{ $project->user->name }}
                                        </a>
                                        <span class="text-neutral-400 font-mono text-[11px]">&#64;{{ $project->user->username }}</span>
                                        <span class="text-neutral-400 font-mono text-[11px]">&bull;</span>
                                        <span class="text-neutral-400 text-[11px]">{{ $project->created_at->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <span class="font-bold text-neutral-500">Anonymous Dev</span>
                                @endif
                            </div>

                            <!-- Right Header: Card Tab Switcher, Prototype Badge & Live Demo Pill -->
                            <div class="flex items-center gap-2 shrink-0">
                                @if($project->prototype_url)
                                    <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-500/15 hover:bg-purple-500/25 text-purple-600 dark:text-purple-300 text-[10px] font-medium border border-purple-500/30 transition shadow-xs group/proto"
                                       title="Buka Prototipe Interaktif (Figma / Framer / v0)">
                                        <svg class="w-3 h-3 text-purple-400 group-hover/proto:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        <span class="font-semibold">Interactive Prototype</span>
                                        <svg class="w-2.5 h-2.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif

                                @if($project->demo_url)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-mono font-bold border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-radar"></span>
                                        <span>Live Demo</span>
                                    </span>
                                @endif

                                <!-- In-Card Tab Switcher [Preview vs Code/Clone] -->
                                <div class="inline-flex p-0.5 rounded-lg bg-neutral-100 dark:bg-white/[0.04] border border-neutral-200 dark:border-white/[0.06] text-[11px] font-medium">
                                    <button @click="cardTab = 'preview'" 
                                            :class="cardTab === 'preview' ? 'bg-white dark:bg-[#202024] text-neutral-900 dark:text-white shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Preview</span>
                                    </button>
                                    <button @click="cardTab = 'code'" 
                                            :class="cardTab === 'code' ? 'bg-white dark:bg-[#202024] text-neutral-900 dark:text-white shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Code & Clone</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Title & Tagline -->
                        <div class="space-y-1.5">
                            <h2 class="text-lg sm:text-xl font-extrabold text-neutral-900 dark:text-white leading-snug group-hover:text-orange-500 transition">
                                <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link">
                                    {{ $project->title }}
                                </a>
                            </h2>
                            <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                                {{ $project->tagline }}
                            </p>
                        </div>

                        <!-- TAB 1: VISUAL PREVIEW -->
                        <div x-show="cardTab === 'preview'">
                            @if($project->thumbnail)
                                <div class="overflow-hidden rounded-xl border border-neutral-200/80 dark:border-white/[0.08] bg-[#050505] relative group/thumb">
                                    <a href="{{ route('projects.show', $project->slug) }}">
                                        <img src="{{ $project->thumbnail }}" 
                                             alt="{{ $project->title }}" 
                                             class="w-full max-h-[380px] object-cover object-top group-hover/thumb:scale-[1.01] transition duration-500">
                                    </a>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-neutral-200 dark:border-white/[0.08] text-center text-xs text-neutral-400 font-mono">
                                    <span>Karya arsitektur kode murni (buka tab Code & Clone untuk instalasi).</span>
                                </div>
                            @endif
                        </div>

                        <!-- TAB 2: CODE & CLONE TERMINAL WINDOW (VERCEL STYLE) -->
                        <div x-show="cardTab === 'code'" x-cloak class="rounded-xl border border-neutral-200 dark:border-white/[0.08] bg-[#09090b] overflow-hidden text-xs font-mono">
                            <div class="code-window-bar flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full window-dot-red"></span>
                                    <span class="w-2.5 h-2.5 rounded-full window-dot-yellow"></span>
                                    <span class="w-2.5 h-2.5 rounded-full window-dot-green"></span>
                                    <span class="ml-2 text-[10px] text-neutral-400 font-mono">quick_clone.sh</span>
                                </div>
                                <button @click="copyText('{{ $project->github_url ? 'git clone ' . $project->github_url . '.git' : 'npm install' }}', 'Clone command copied!')" 
                                        class="inline-flex items-center gap-1 text-[10px] text-orange-500 hover:text-orange-400 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                    <span>Copy Command</span>
                                </button>
                            </div>

                            <div class="p-4 space-y-3">
                                <div class="flex items-center gap-2 text-neutral-300 select-all overflow-x-auto">
                                    <span class="text-emerald-400 select-none">$</span>
                                    <code>{{ $project->github_url ? 'git clone ' . $project->github_url . '.git' : 'git clone https://github.com/developer/' . $project->slug . '.git' }}</code>
                                </div>

                                <div class="pt-2 border-t border-white/[0.05] text-[11px] text-neutral-400 flex items-center justify-between flex-wrap gap-2">
                                    <span>Kategori: <strong class="text-neutral-200">{{ $project->category->name }}</strong></span>
                                    @if($project->github_url)
                                        <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-orange-400 hover:underline">
                                            <span>Lihat GitHub Repository</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tech Stacks -->
                        @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
                            <div class="flex flex-wrap gap-1.5 font-mono pt-1">
                                @foreach($project->tech_stacks as $tech)
                                    <a href="{{ route('projects.index', ['tech' => $tech]) }}" class="linear-pill">
                                        #{{ $tech }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <!-- Action Bar (Sleek Vercel Engineering Bar) -->
                        <div class="flex items-center justify-between gap-2 pt-3 border-t border-neutral-100 dark:border-white/[0.06] flex-wrap">
                            
                            <!-- Left: Upvote Pill & Live Links -->
                            <div class="flex items-center gap-2">
                                <!-- Upvote / Downvote Pill -->
                                <div class="flex items-center bg-neutral-100 dark:bg-[#141418] border border-neutral-200 dark:border-white/[0.08] rounded-xl p-0.5">
                                    <button @click="vote('up')" 
                                            :class="{ 'text-orange-500 bg-orange-500/10 font-bold': userVote === 'up', 'text-neutral-600 dark:text-neutral-400 hover:text-orange-500': userVote !== 'up' }"
                                            class="btn-upvote flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-mono" x-text="score"></span>
                                    </button>
                                    <button @click="vote('down')" 
                                            :class="{ 'text-rose-500 bg-rose-500/10': userVote === 'down', 'text-neutral-400 hover:text-rose-500': userVote !== 'down' }"
                                            class="p-1.5 rounded-lg text-xs transition active:scale-95" 
                                            title="Downvote">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>

                                @if($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                                       class="hidden xs:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-semibold text-xs border border-emerald-500/20 transition">
                                        <span>Demo</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif

                                @if($project->prototype_url)
                                    <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer"
                                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-300 font-semibold text-xs border border-purple-500/30 transition shadow-2xs active:scale-95"
                                       title="Buka Prototipe Interaktif (Figma / Framer / v0)">
                                        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        <span>Prototype</span>
                                        <svg class="w-3 h-3 text-purple-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif

                                @if($project->github_url)
                                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer"
                                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-white/[0.04] hover:bg-neutral-200 dark:hover:bg-white/[0.08] text-neutral-700 dark:text-neutral-300 font-semibold text-xs border border-neutral-200 dark:border-white/[0.08] transition">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                        </svg>
                                        <span>Code</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>

                            <!-- Right Actions: Reviews, Bookmark, Inspect, Share -->
                            <div class="flex items-center gap-1 text-neutral-400 text-xs">
                                <!-- Comment / Review count -->
                                <a href="{{ route('projects.show', $project->slug) }}#komentar" 
                                   class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-white/[0.04] text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span class="font-mono text-[11px]">{{ $project->comments_count }}</span>
                                </a>

                                <!-- Bookmark Button -->
                                <button @click="toggleBookmark()" 
                                        :class="{ 'text-orange-500': isBookmarked, 'text-neutral-400 hover:text-orange-500': !isBookmarked }"
                                        class="btn-bookmark p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-white/[0.04] transition active:scale-95" 
                                        title="Simpan ke Koleksi">
                                    <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                </button>

                                <!-- Quick Inspect Button -->
                                <button @click="openInspect({
                                    id: {{ $project->id }},
                                    title: '{{ addslashes($project->title) }}',
                                    tagline: '{{ addslashes($project->tagline) }}',
                                    description: '{{ addslashes(str_replace(["\r", "\n"], ' ', Str::limit($project->description, 280))) }}',
                                    authorName: '{{ addslashes($project->user->name ?? 'Dev') }}',
                                    authorUsername: '{{ $project->user->username ?? 'dev' }}',
                                    authorAvatar: '{{ $project->user->avatar ?? '' }}',
                                    categoryName: '{{ $project->category->name }}',
                                    techs: {{ json_encode($project->tech_stacks ?? []) }},
                                    score: score,
                                    demoUrl: '{{ $project->demo_url }}',
                                    githubUrl: '{{ $project->github_url }}',
                                    prototypeUrl: '{{ $project->prototype_url }}',
                                    slug: '{{ $project->slug }}',
                                    installCmd: '{{ $project->github_url ? 'git clone ' . $project->github_url . '.git' : 'npm install' }}'
                                })"
                                class="p-2 rounded-lg text-neutral-400 hover:text-orange-500 hover:bg-neutral-100 dark:hover:bg-white/[0.04] font-mono transition"
                                title="Quick Inspect Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                </button>

                                <!-- Share Button -->
                                <button @click="copyLink()" 
                                        class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-white/[0.04] hover:text-neutral-900 dark:hover:text-white transition"
                                        title="Salin Tautan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                    </svg>
                                </button>
                            </div>

                        </div>

                    </article>
                @empty
                    <div class="spotlight-card p-12 text-center space-y-3 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                        <svg class="w-12 h-12 text-neutral-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <h3 class="font-bold text-neutral-900 dark:text-white text-base">Belum ada karya codingan ditemukan</h3>
                        <p class="text-xs text-neutral-500">Coba ubah kata kunci pencarian atau bersihkan filter kategori.</p>
                        @auth
                            <a href="{{ route('projects.create') }}" class="btn-vercel-primary inline-flex items-center gap-1.5 mt-2 px-5 py-2.5 text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Pamerkan Karya Codingan</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-vercel-primary inline-flex items-center gap-1.5 mt-2 px-5 py-2.5 text-xs font-semibold shadow-xs transition">
                                <span>Masuk untuk Pamerkan Karya</span>
                            </a>
                        @endauth
                    </div>
                @endforelse
            </div>

            <!-- PAGINATION -->
            <div class="pt-4">
                {{ $projects->links() }}
            </div>

        </div>

        <!-- RIGHT COLUMN: ACTIVE COMMUNITY SIDEBAR (lg:col-span-4) -->
        <aside class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">

            <!-- WIDGET 1: CALL TO ACTION MINI PAMER -->
            <div class="spotlight-card p-5 bg-gradient-to-br from-orange-500/10 via-amber-500/5 to-transparent border border-orange-500/20 dark:border-orange-500/20 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-orange-500">
                        Pamer Karya Baru
                    </span>
                </div>
                <h3 class="font-bold text-sm text-neutral-900 dark:text-white leading-snug">
                    Punya proyek kodingan yang baru selesai dibuild?
                </h3>
                <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Tunjukkan arsitektur kode dan live demo aplikasimu kepada ribuan developer lain.
                </p>
                @auth
                    <a href="{{ route('projects.create') }}" 
                       class="btn-vercel-primary w-full py-2.5 px-4 text-center text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Pamerkan Kodingan</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="btn-vercel-primary w-full py-2.5 px-4 text-center text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center justify-center gap-2">
                        <span>Masuk untuk Pamerkan Kodingan</span>
                    </a>
                @endauth
            </div>

            <!-- WIDGET 2: LIVE DEPLOY ACTIVITY STREAM (REAL-TIME PULSE) -->
            <div class="spotlight-card p-4 sm:p-5 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] space-y-3">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/[0.06] pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-radar"></span>
                        <h4 class="font-bold text-xs uppercase font-mono tracking-wider text-neutral-900 dark:text-white">
                            Live Build Stream
                        </h4>
                    </div>
                    <span class="text-[10px] font-mono text-emerald-500 font-semibold">Live Feed</span>
                </div>

                <div class="space-y-3">
                    @forelse($recentDeploys ?? [] as $deploy)
                        <div class="flex items-start gap-2.5 text-xs group">
                            <a href="{{ route('profile.show', $deploy->user->username ?? 'dev') }}" class="shrink-0 mt-0.5">
                                <img src="{{ $deploy->user->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($deploy->user->name ?? 'Dev') }}" 
                                     alt="{{ $deploy->user->name ?? 'Dev' }}" 
                                     class="w-6 h-6 rounded-lg bg-neutral-100 dark:bg-neutral-800 object-cover">
                            </a>
                            <div class="min-w-0 flex-1">
                                <p class="text-neutral-600 dark:text-neutral-300 leading-snug line-clamp-2">
                                    <strong class="text-neutral-900 dark:text-white font-medium">{{ $deploy->user->name ?? 'Dev' }}</strong>
                                    memamerkan 
                                    <a href="{{ route('projects.show', $deploy->slug) }}" class="text-orange-500 hover:underline font-semibold">
                                        {{ $deploy->title }}
                                    </a>
                                </p>
                                <span class="text-[10px] text-neutral-400 font-mono mt-0.5 block">
                                    {{ $deploy->created_at->diffForHumans() }} &bull; {{ $deploy->category->name }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 font-mono text-center py-2">Belum ada aktivitas deploy.</p>
                    @endforelse
                </div>
            </div>

            <!-- WIDGET 3: TOP DEVELOPER LEADERBOARD -->
            <div class="spotlight-card p-4 sm:p-5 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] space-y-3">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/[0.06] pb-2.5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <h4 class="font-bold text-xs uppercase font-mono tracking-wider text-neutral-900 dark:text-white">
                            Top Developers
                        </h4>
                    </div>
                    <span class="text-[10px] font-mono text-neutral-400">Paling Aktif</span>
                </div>

                <div class="space-y-2.5">
                    @forelse($topDevelopers ?? [] as $index => $dev)
                        <div class="flex items-center justify-between gap-2 p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-white/[0.03] transition">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-4 text-center font-mono font-bold text-xs {{ $index === 0 ? 'text-amber-400' : ($index === 1 ? 'text-neutral-300' : ($index === 2 ? 'text-amber-600' : 'text-neutral-500')) }}">
                                    #{{ $index + 1 }}
                                </span>
                                <a href="{{ route('profile.show', $dev->username) }}" class="shrink-0">
                                    <img src="{{ $dev->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($dev->name) }}" 
                                         alt="{{ $dev->name }}" 
                                         class="w-7 h-7 rounded-lg bg-neutral-200 dark:bg-neutral-800 object-cover ring-1 ring-neutral-200 dark:ring-white/10">
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('profile.show', $dev->username) }}" class="font-bold text-xs text-neutral-900 dark:text-white hover:text-orange-500 transition block truncate">
                                        {{ $dev->name }}
                                    </a>
                                    <span class="text-[10px] text-neutral-400 font-mono block truncate">&#64;{{ $dev->username }}</span>
                                </div>
                            </div>

                            <span class="font-mono text-xs font-bold text-orange-500 shrink-0">
                                {{ $dev->reputation_points }} <span class="text-[10px] text-neutral-400 font-normal">pts</span>
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 font-mono text-center py-2">Belum ada data developer.</p>
                    @endforelse
                </div>
            </div>

            <!-- WIDGET 4: RADAR TECH STACKS TERPOPULER -->
            <div class="spotlight-card p-4 sm:p-5 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] space-y-3">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/[0.06] pb-2.5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h4 class="font-bold text-xs uppercase font-mono tracking-wider text-neutral-900 dark:text-white">
                            Radar Tech Stacks
                        </h4>
                    </div>
                    <span class="text-[10px] font-mono text-neutral-400">Filter Cepat</span>
                </div>

                <div class="flex flex-wrap gap-1.5 font-mono">
                    @foreach($trendingTech ?? ['Laravel', 'Vue', 'React', 'Go', 'Python', 'TailwindCSS', 'TypeScript', 'AI'] as $tech)
                        <a href="{{ route('projects.index', ['tech' => $tech]) }}" 
                           class="linear-pill hover:border-orange-500/40 hover:text-orange-400 transition">
                            #{{ $tech }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- WIDGET 5: RECENT PEER REVIEWS STREAM -->
            @if(isset($recentReviews) && count($recentReviews) > 0)
                <div class="spotlight-card p-4 sm:p-5 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] space-y-3">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/[0.06] pb-2.5">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <h4 class="font-bold text-xs uppercase font-mono tracking-wider text-neutral-900 dark:text-white">
                                Peer Code Reviews
                            </h4>
                        </div>
                        <span class="text-[10px] font-mono text-amber-500">Terbaru</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($recentReviews as $rev)
                            <div class="text-xs space-y-1">
                                <div class="flex items-center justify-between gap-1 text-[11px]">
                                    <span class="font-bold text-neutral-900 dark:text-neutral-200 truncate">
                                        {{ $rev->authorName() }}
                                    </span>
                                    <span class="text-neutral-400 font-mono text-[10px]">
                                        {{ $rev->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-neutral-600 dark:text-neutral-400 text-xs line-clamp-2 leading-relaxed">
                                    "{{ $rev->content }}"
                                </p>
                                @if($rev->project)
                                    <a href="{{ route('projects.show', $rev->project->slug) }}#komentar" class="text-[11px] font-mono text-orange-500 hover:underline inline-block truncate max-w-full">
                                        pada: {{ $rev->project->title }} →
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </aside>

    </div>

    <!-- INTERACTIVE INSPECT & QUICK CODE SLIDE-OVER DRAWER -->
    <div x-show="inspectDrawer.open" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden" 
         @keydown.escape.window="closeInspect()">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-opacity" 
             @click="closeInspect()"></div>

        <!-- Slide Drawer Panel -->
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md spotlight-card h-full rounded-none sm:rounded-l-3xl shadow-2xl overflow-y-auto"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <div class="h-full flex flex-col justify-between p-6 space-y-6">
                    
                    <!-- Drawer Header -->
                    <div class="flex items-start justify-between gap-3 border-b border-neutral-100 dark:border-white/[0.08] pb-4">
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-mono font-bold uppercase tracking-widest text-orange-500 block mb-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                                <span>QUICK INSPECT</span>
                            </span>
                            <h2 class="text-lg font-bold text-neutral-900 dark:text-white leading-tight" x-text="inspectDrawer.title"></h2>
                            <p class="text-xs text-neutral-500 mt-1 font-mono" x-text="'Kategori: ' + inspectDrawer.categoryName"></p>
                        </div>
                        <button @click="closeInspect()" class="text-neutral-400 hover:text-white p-1 rounded-lg transition" title="Tutup (Esc)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Author Info Card -->
                    <div class="p-3 rounded-xl bg-neutral-50 dark:bg-white/[0.03] border border-neutral-200 dark:border-white/[0.06] flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <img :src="inspectDrawer.authorAvatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=Dev'" 
                                 class="w-8 h-8 rounded-lg bg-neutral-200 dark:bg-neutral-800 object-cover">
                            <div>
                                <p class="text-xs font-bold text-neutral-900 dark:text-white" x-text="inspectDrawer.authorName"></p>
                                <p class="text-[10px] text-neutral-400 font-mono" x-text="'@' + inspectDrawer.authorUsername"></p>
                            </div>
                        </div>
                        <a :href="'/u/' + inspectDrawer.authorUsername" class="inline-flex items-center gap-1 text-xs font-mono text-orange-500 hover:underline">
                            <span>Profil</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Quick Specs Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <div class="p-2.5 rounded-xl bg-neutral-50 dark:bg-white/[0.03] border border-neutral-200 dark:border-white/[0.06]">
                            <span class="text-[10px] text-neutral-400 block">Skor Komunitas</span>
                            <span class="font-bold text-orange-500 text-sm" x-text="'+' + inspectDrawer.score + ' Upvotes'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-50 dark:bg-white/[0.03] border border-neutral-200 dark:border-white/[0.06]">
                            <span class="text-[10px] text-neutral-400 block">Status Deployment</span>
                            <span class="font-bold text-emerald-500 text-sm flex items-center gap-1 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Verified</span>
                            </span>
                        </div>
                    </div>

                    <!-- Quick Install / Clone Terminal Box -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold text-neutral-400 uppercase tracking-wider">
                                Quick Run / Clone
                            </span>
                            <button @click="copyText(inspectDrawer.installCmd, 'Command berhasil disalin!')" 
                                    class="inline-flex items-center gap-1 text-[11px] font-mono text-orange-500 hover:underline">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                <span>Copy Command</span>
                            </button>
                        </div>
                        <div class="terminal-window p-3 text-xs text-neutral-300 flex items-center justify-between gap-2 overflow-x-auto">
                            <span class="text-emerald-400 select-none">$</span>
                            <code class="text-neutral-200 font-mono text-xs flex-1 select-all" x-text="inspectDrawer.installCmd"></code>
                        </div>
                    </div>

                    <!-- Tagline & Description -->
                    <div class="space-y-1 text-xs">
                        <span class="text-[11px] font-mono font-bold text-neutral-400 uppercase tracking-wider block">
                            Ringkasan
                        </span>
                        <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed" x-text="inspectDrawer.tagline"></p>
                    </div>

                    <!-- Tech Stack Tags -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-mono font-bold text-neutral-400 uppercase tracking-wider block">
                            Tech Stacks & Tools
                        </span>
                        <div class="flex flex-wrap gap-1 font-mono">
                            <template x-for="tech in inspectDrawer.techs" :key="tech">
                                <span class="linear-pill" x-text="'#' + tech"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Drawer Bottom Action Links -->
                    <div class="pt-4 border-t border-neutral-100 dark:border-white/[0.08] flex items-center gap-2">
                        <a :href="'/project/' + inspectDrawer.slug" 
                           class="btn-vercel-primary flex-1 py-2.5 text-center text-xs rounded-xl shadow-xs active:scale-95 transition inline-flex items-center justify-center gap-1.5">
                            <span>Buka Halaman Lengkap</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <template x-if="inspectDrawer.demoUrl">
                            <a :href="inspectDrawer.demoUrl" target="_blank" rel="noopener noreferrer"
                               class="px-4 py-2.5 bg-neutral-100 dark:bg-white/[0.04] hover:bg-neutral-200 dark:hover:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold text-xs rounded-xl transition inline-flex items-center gap-1">
                                <span>Demo</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </template>
                        <template x-if="inspectDrawer.prototypeUrl">
                            <a :href="inspectDrawer.prototypeUrl" target="_blank" rel="noopener noreferrer"
                               class="px-4 py-2.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-300 font-semibold text-xs rounded-xl border border-purple-500/30 transition inline-flex items-center gap-1"
                               title="Prototipe Interaktif (Figma / Framer / v0)">
                                <span>Proto</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </template>
                        <template x-if="inspectDrawer.githubUrl">
                            <a :href="inspectDrawer.githubUrl" target="_blank" rel="noopener noreferrer"
                               class="px-4 py-2.5 bg-neutral-100 dark:bg-white/[0.04] hover:bg-neutral-200 dark:hover:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold text-xs rounded-xl transition inline-flex items-center gap-1"
                                title="GitHub Repository">
                                <span>Repo</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
