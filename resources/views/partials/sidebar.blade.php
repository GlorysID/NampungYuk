<div class="h-full flex flex-col justify-between overflow-y-auto custom-scrollbar p-4 lg:p-5 select-none text-xs">
    
    <!-- TOP SECTION: BRAND & NAVIGATION -->
    <div class="space-y-6">
        
        <!-- Brand Header -->
        <div class="flex items-center justify-between gap-3 px-1.5 pt-1">
            <x-logo size="md" :show-tagline="true" />

            <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.5 rounded bg-[#e6eaee] dark:bg-[#1e2530] text-[#5c6979] dark:text-[#7e8a9a] border border-[#d5dbe2] dark:border-[#262d3a]">
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
            <p class="px-2 text-[10px] font-mono uppercase font-bold tracking-wider text-[#5c6979] dark:text-[#7e8a9a]">
                Menu Utama
            </p>

            @php
                $currentTab = request('tab');
                $isAllFeed = request()->routeIs('projects.index') && ! $currentTab && ! request('kategori') && ! request('tech');
            @endphp

            <!-- Semua Karya (Feed) -->
            <a href="{{ route('projects.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-md font-medium transition {{ $isAllFeed ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $isAllFeed ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Semua Karya</span>
                </div>
                @if($isAllFeed)
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>

            <!-- Following (Mengikuti) -->
            @auth
                <a href="{{ route('projects.index', ['tab' => 'following']) }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-md font-medium transition {{ $currentTab === 'following' ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ $currentTab === 'following' ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3 3 3 0 013 3z"/>
                        </svg>
                        <span>Mengikuti</span>
                    </div>
                    @if($currentTab === 'following')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                    @endif
                </a>
            @endauth

            <!-- Trending -->
            <a href="{{ route('projects.index', ['tab' => 'trend']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'trend' ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'trend' ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    <span>Trending</span>
                </div>
                @if($currentTab === 'trend')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>

            <!-- Prototipe Interaktif -->
            <a href="{{ route('projects.index', ['tab' => 'prototype']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'prototype' ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span>Prototipe Interaktif</span>
                </div>
                @if($currentTab === 'prototype')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>

            <!-- Terbaru -->
            <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'terbaru' ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'terbaru' ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Terbaru</span>
                </div>
                @if($currentTab === 'terbaru')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>

            <!-- Populer -->
            <a href="{{ route('projects.index', ['tab' => 'populer']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $currentTab === 'populer' ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'populer' ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span>Populer</span>
                </div>
                @if($currentTab === 'populer')
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>
        </div>

        <!-- Section 2: Explore (Kategori Kodingan) -->
        <div class="space-y-1 pt-2">
            <p class="px-2 text-[10px] font-mono uppercase font-bold tracking-wider text-[#5c6979] dark:text-[#7e8a9a]">
                Kategori Kodingan
            </p>

            @if(isset($categories) && count($categories) > 0)
                <div class="space-y-0.5">
                    @foreach($categories as $category)
                        @php $isCatActive = (request('kategori') === $category->slug); @endphp
                        <a href="{{ route('projects.index', ['kategori' => $category->slug]) }}" 
                           class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition {{ $isCatActive ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                            <span class="truncate">{{ $category->name }}</span>
                            <span class="text-[10px] font-mono tabular-nums px-1.5 py-0.2 rounded {{ $isCatActive ? 'bg-[#50d2c1]/20 text-[#0e9c8b] dark:text-[#6ee7d5]' : 'bg-[#e6eaee] dark:bg-[#1e2530] text-[#5c6979]' }}">
                                {{ $category->projects_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Section 3: Tech Stacks -->
        <div class="space-y-1.5 pt-2 border-t border-[#d5dbe2] dark:border-[#262d3a]">
            <p class="px-2 text-[10px] font-mono uppercase font-bold tracking-wider text-[#5c6979] dark:text-[#7e8a9a]">
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

        <!-- Section 4: Library & Akun -->
        <div class="space-y-1 pt-2 border-t border-[#d5dbe2] dark:border-[#262d3a]">
            <p class="px-2 text-[10px] font-mono uppercase font-bold tracking-wider text-[#5c6979] dark:text-[#7e8a9a]">
                Library
            </p>

            @php $isBookmarks = request()->routeIs('projects.bookmarks'); @endphp
            <a href="{{ route('projects.bookmarks') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $isBookmarks ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $isBookmarks ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span>Koleksi Tersimpan</span>
                </div>
                @if($isBookmarks)
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                @endif
            </a>

            @auth
                @php $isProfile = request()->routeIs('profile.show') && request('username') === Auth::user()->username; @endphp
                <a href="{{ route('profile.show', Auth::user()->username) }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition {{ $isProfile ? 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ $isProfile ? 'text-[#0e9c8b] dark:text-[#50d2c1]' : 'opacity-70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil Saya</span>
                    </div>
                </a>
            @endauth
        </div>

    </div>

    <!-- BOTTOM FOOTER -->
    <div class="pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a] text-[11px] text-[#5c6979] dark:text-[#7e8a9a] flex items-center justify-between">
        <span>NampungYuk &copy; {{ date('Y') }}</span>
        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" rel="noopener noreferrer" class="hover:text-[#10161f] dark:hover:text-white transition">
            GitHub
        </a>
    </div>

</div>
