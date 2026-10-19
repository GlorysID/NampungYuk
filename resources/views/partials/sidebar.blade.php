<div class="h-full flex flex-col justify-between overflow-hidden p-3.5 xl:p-4 select-none text-xs bg-white dark:bg-[#0a0a0a]">
    
    <!-- TOP SECTION: BRAND & NAVIGATION -->
    <div class="space-y-3 xl:space-y-3.5">
        
        <!-- Brand Header -->
        <div class="flex items-center justify-between gap-3 px-1 pt-0.5">
            <x-logo size="md" :show-tagline="true" />

            <span class="text-[10px] font-mono tabular-nums px-2 py-0.5 rounded-full bg-[#f4f4f5] dark:bg-[#18181b] text-[#63636b] dark:text-[#a0a0a0] border border-[#e4e4e7] dark:border-[#27272a]">
                v1.0
            </span>
        </div>

        <!-- Section 1: Menu Utama -->
        <div class="space-y-0.5">
            <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#71717a] dark:text-[#a1a1aa] mb-1">
                Menu Utama
            </p>

            @php
                $currentTab = request('tab');
                $isAllFeed = request()->routeIs('projects.index') && ! $currentTab && ! request('kategori') && ! request('tech');
            @endphp

            <!-- Semua Karya (Feed) -->
            <a href="{{ route('projects.index') }}" 
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $isAllFeed ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $isAllFeed ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Semua Karya</span>
                </div>
                @if($isAllFeed)
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>

            <!-- Following (Mengikuti) -->
            @auth
                <a href="{{ route('projects.index', ['tab' => 'following']) }}" 
                   class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $currentTab === 'following' ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ $currentTab === 'following' ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3 3 3 0 013 3z"/>
                        </svg>
                        <span>Mengikuti</span>
                    </div>
                    @if($currentTab === 'following')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                    @endif
                </a>
            @endauth

            <!-- Trending -->
            <a href="{{ route('projects.index', ['tab' => 'trend']) }}" 
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $currentTab === 'trend' ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'trend' ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    <span>Trending</span>
                </div>
                @if($currentTab === 'trend')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>

            <!-- Prototipe Interaktif -->
            <a href="{{ route('projects.index', ['tab' => 'prototype']) }}" 
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $currentTab === 'prototype' ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span>Prototipe Interaktif</span>
                </div>
                @if($currentTab === 'prototype')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>

            <!-- Terbaru -->
            <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}" 
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $currentTab === 'terbaru' ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'terbaru' ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Terbaru</span>
                </div>
                @if($currentTab === 'terbaru')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>

            <!-- Populer -->
            <a href="{{ route('projects.index', ['tab' => 'populer']) }}" 
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ $currentTab === 'populer' ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'populer' ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span>Populer</span>
                </div>
                @if($currentTab === 'populer')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>
        </div>

        <!-- Section 2: Komunitas & Obrolan -->
        <div class="space-y-0.5 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a]">
            <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#71717a] dark:text-[#a1a1aa] mb-1">
                Komunitas
            </p>

            <a href="{{ route('communities.index') }}"
               class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ request()->routeIs('communities.*') ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ request()->routeIs('communities.*') ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Komunitas</span>
                </div>
                @if(request()->routeIs('communities.*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                @endif
            </a>

            @auth
                <a href="{{ route('chat.index') }}"
                   class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ request()->routeIs('chat.*') ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ request()->routeIs('chat.*') ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span>Obrolan</span>
                    </div>
                    @if(request()->routeIs('chat.*'))
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                    @endif
                </a>
            @else
                <button type="button" 
                        @click="window.openAuthModal('login')"
                        class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b] transition cursor-pointer">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span>Obrolan</span>
                    </div>
                </button>
            @endauth
        </div>

        @if(auth()->check() && auth()->user()->is_admin)
            @php $pendingReportsCount = \App\Models\Report::pending()->count(); @endphp
            <div class="space-y-0.5 pt-2 border-t border-rose-500/20">
                <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-rose-500 flex items-center justify-between mb-1">
                    <span>Admin Control</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                </p>
                <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}" 
                   class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ request()->get('tab') === 'all_users' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold border border-purple-500/30' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Kelola Pengguna</span>
                    </div>
                </a>
                <a href="{{ route('admin.reports.index') }}" 
                   class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition {{ request()->routeIs('admin.*') && request()->get('tab') !== 'all_users' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold border border-rose-500/30' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Laporan Pengaduan</span>
                    </div>
                    @if($pendingReportsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">
                            {{ $pendingReportsCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endif

        <!-- Section 3: Kategori -->
        @if(request()->routeIs('communities.*'))
            @php $sidebarCommunities = \App\Models\Community::visibleTo(auth()->id())->orderByDesc('members_count')->take(5)->get(); @endphp
            @if($sidebarCommunities->count() > 0)
            <div class="space-y-0.5 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a]">
                <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#71717a] dark:text-[#a1a1aa] mb-1">
                    Kategori Komunitas
                </p>
                @foreach($sidebarCommunities as $sc)
                    @php $scActive = request('kategori') === $sc->slug; @endphp
                    <a href="{{ route('communities.show', $sc->slug) }}"
                       class="flex items-center justify-between px-3 py-1 rounded-lg text-xs transition {{ $scActive ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                        <span class="truncate">{{ $sc->name }}</span>
                        <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded bg-[#f4f4f5] dark:bg-[#18181b] text-[#71717a]">{{ $sc->members_count }}</span>
                    </a>
                @endforeach
            </div>
            @endif
        @elseif(isset($categories) && count($categories) > 0)
            @php
                $activeCatSlug = request('kategori');
                $topCategories = $categories->sortByDesc('projects_count')->take(5);
            @endphp
            <div class="space-y-0.5 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a]">
                <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#71717a] dark:text-[#a1a1aa] mb-1">
                    Kategori Kodingan
                </p>
                @foreach($topCategories as $category)
                    @php $isCatActive = ($activeCatSlug === $category->slug); @endphp
                    <a href="{{ route('projects.index', ['kategori' => $category->slug]) }}"
                       class="flex items-center justify-between px-3 py-1 rounded-lg text-xs transition {{ $isCatActive ? 'bg-[#0070f3]/10 dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f4f4f5] dark:hover:bg-[#18181b]' }}">
                        <span class="truncate">{{ $category->name }}</span>
                        <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded {{ $isCatActive ? 'bg-[#0070f3]/20 text-[#0070f3] dark:text-[#3291ff]' : 'bg-[#f4f4f5] dark:bg-[#18181b] text-[#71717a]' }}">{{ $category->projects_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Section 4: Tech Stacks -->
        <div class="space-y-1 pt-2 border-t border-[#e4e4e7] dark:border-[#27272a]">
            <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#71717a] dark:text-[#a1a1aa] mb-1">
                Tech Stacks
            </p>
            <div class="flex flex-wrap gap-1 px-1">
                @foreach(['Laravel', 'Vue', 'React', 'TailwindCSS', 'Go', 'AI'] as $stack)
                    <a href="{{ route('projects.index', ['tech' => $stack]) }}" class="tech-pill text-[10px]">
                        #{{ $stack }}
                    </a>
                @endforeach
            </div>
        </div>

    </div>

</div>
