<div class="h-full flex flex-col justify-between overflow-y-auto custom-scrollbar p-4 lg:p-5 select-none text-xs">
    
    <!-- TOP SECTION: BRAND & NAVIGATION -->
    <div class="space-y-6">
        
        <!-- Brand Header -->
        <div class="flex items-center justify-between gap-3 px-1.5 pt-1">
            <x-logo size="md" :show-tagline="true" />

            <span class="text-[10px] font-mono tabular-nums px-2 py-1 rounded-full bg-[#eeeeef] dark:bg-[#171717] text-[#63636b] dark:text-[#a0a0a0] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                v1.0
            </span>
        </div>

        <!-- Primary Action CTA -->
        <div class="pt-1">
            <a href="{{ route('projects.create') }}" 
               class="btn-primary w-full py-2.5 px-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Pamerkan Project</span>
            </a>
        </div>

        <!-- Section 1: Discover -->
        <div class="space-y-1">
            <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">
                Menu Utama
            </p>

            @php
                $currentTab = request('tab');
                $isAllFeed = request()->routeIs('projects.index') && ! $currentTab && ! request('kategori') && ! request('tech');
            @endphp

            <!-- Semua Karya (Feed) -->
            <a href="{{ route('projects.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition {{ $isAllFeed ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
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
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition {{ $currentTab === 'following' ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
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
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'trend' ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
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
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'prototype' ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'terbaru' ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
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
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'populer' ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
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

        <!-- Section: Komunitas (separate from Menu Utama) -->
        <div class="space-y-1 pt-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
            <p class="px-2 pt-2 text-[11px] font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">
                Komunitas
            </p>
            <a href="{{ route('communities.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('communities.*') ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ request()->routeIs('communities.*') ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Komunitas</span>
                </div>
                @if(request()->routeIs('communities.*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:text-[#3291ff]"></span>
                @endif
            </a>

            <a href="{{ route('chat.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('chat.*') ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ request()->routeIs('chat.*') ? 'text-[#0070f3] dark:text-[#3291ff]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Obrolan</span>
                </div>
                @if(request()->routeIs('chat.*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:text-[#3291ff]"></span>
                @endif
            </a>
        </div>

        <!-- Section: Kategori (contextual — kodingan di feed, komunitas di halaman komunitas) -->
        @if(request()->routeIs('communities.*'))
            {{-- Kategori komunitas: list komunitas teratas --}}
            @php $sidebarCommunities = \App\Models\Community::visibleTo(auth()->id())->orderByDesc('members_count')->take(6)->get(); @endphp
            @if($sidebarCommunities->count() > 0)
            <div class="space-y-1 pt-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
                <p class="px-2 pt-2 text-[11px] font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">
                    Kategori Komunitas
                </p>
                @foreach($sidebarCommunities as $sc)
                    @php $scActive = request('kategori') === $sc->slug; @endphp
                    <a href="{{ route('communities.show', $sc->slug) }}"
                       class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs transition {{ $scActive ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                        <span class="truncate">{{ $sc->name }}</span>
                        <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded bg-[#eeeeef] dark:bg-[#171717] text-[#63636b]">{{ $sc->members_count }}</span>
                    </a>
                @endforeach
            </div>
            @endif
        @elseif(isset($categories) && count($categories) > 0)
            {{-- Kategori kodingan: tampil di feed/project --}}
            @php
                $activeCatSlug = request('kategori');
                $topCategories = $categories->sortByDesc('projects_count')->take(6);
                $restCategories = $categories->sortByDesc('projects_count')->slice(6);
                $activeInRest = $activeCatSlug && $restCategories->contains('slug', $activeCatSlug);
            @endphp
            <div class="space-y-1 pt-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
                <p class="px-2 pt-2 text-[11px] font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">
                    Kategori Kodingan
                </p>
                <div class="space-y-0.5" x-data="{ showAllCats: {{ $activeInRest ? 'true' : 'false' }} }">
                    @foreach($topCategories as $category)
                        @php $isCatActive = ($activeCatSlug === $category->slug); @endphp
                        <a href="{{ route('projects.index', ['kategori' => $category->slug]) }}"
                           class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs transition {{ $isCatActive ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                            <span class="truncate">{{ $category->name }}</span>
                            <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded {{ $isCatActive ? 'bg-[#3291ff]/20 text-[#0070f3] dark:text-[#47a8ff]' : 'bg-[#eeeeef] dark:bg-[#171717] text-[#63636b]' }}">{{ $category->projects_count }}</span>
                        </a>
                    @endforeach

                    @if($restCategories->count() > 0)
                        <div x-show="showAllCats" x-cloak class="space-y-0.5">
                            @foreach($restCategories as $category)
                                @php $isCatActive = ($activeCatSlug === $category->slug); @endphp
                                <a href="{{ route('projects.index', ['kategori' => $category->slug]) }}"
                                   class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs transition {{ $isCatActive ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                                    <span class="truncate">{{ $category->name }}</span>
                                    <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded {{ $isCatActive ? 'bg-[#3291ff]/20 text-[#0070f3] dark:text-[#47a8ff]' : 'bg-[#eeeeef] dark:bg-[#171717] text-[#63636b]' }}">{{ $category->projects_count }}</span>
                                </a>
                            @endforeach
                        </div>
                        <button type="button" @click="showAllCats = !showAllCats"
                                class="w-full flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl text-[11px] font-medium text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition">
                            <span x-text="showAllCats ? 'Sembunyikan' : 'Lihat semua ({{ $categories->count() }})'"></span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="showAllCats ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Section 3: Tech Stacks -->
        <div class="space-y-1.5 pt-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
            <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">
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
