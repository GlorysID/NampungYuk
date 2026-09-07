<div class="h-full flex flex-col justify-between overflow-y-auto custom-scrollbar p-4 lg:p-5 select-none">
    
    <!-- TOP SECTION: BRAND & NAVIGATION -->
    <div class="space-y-6">
        
        <!-- Brand Header -->
        <div class="flex items-center justify-between gap-3 px-1.5 pt-1">
            <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-orange-500 via-amber-500 to-orange-600 flex items-center justify-center text-white font-mono font-bold text-xs shadow-sm group-hover:scale-105 active:scale-95 transition">
                    <span>{;}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-extrabold tracking-tight text-neutral-900 dark:text-white leading-none flex items-center gap-1.5">
                        Nampung<span class="text-orange-500">Yuk</span>
                    </span>
                    <span class="text-[9px] font-mono uppercase tracking-widest text-neutral-400 dark:text-neutral-500 font-bold mt-0.5">
                        Dev Showcase
                    </span>
                </div>
            </a>

            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-md bg-neutral-100 dark:bg-white/[0.06] text-neutral-500 border border-neutral-200 dark:border-white/[0.08]">
                v1.2
            </span>
        </div>

        <!-- Primary CTA: Pamer Kodingan Button -->
        <div class="pt-1">
            @auth
                <a href="{{ route('projects.create') }}" 
                   class="btn-vercel-primary w-full flex items-center justify-center gap-2 py-2.5 px-3 text-xs font-semibold rounded-xl shadow-xs transition active:scale-98">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Pamer Kodingan</span>
                </a>
            @else
                <a href="{{ route('login') }}" 
                   class="btn-vercel-primary w-full flex items-center justify-center gap-2 py-2.5 px-3 text-xs font-semibold rounded-xl shadow-xs transition active:scale-98">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Pamer Kodingan</span>
                </a>
            @endauth
        </div>

        <!-- Section 1: Main Exploration Menu -->
        <div class="space-y-1">
            <p class="px-2 text-[10px] font-mono uppercase font-bold tracking-wider text-neutral-400 dark:text-neutral-500">
                Menu Utama
            </p>

            @php
                $currentTab = request('tab');
                $isAllFeed = request()->routeIs('projects.index') && ! $currentTab && ! request('kategori') && ! request('tech');
            @endphp

            <!-- Semua Karya (Feed) -->
            <a href="{{ route('projects.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $isAllFeed ? 'bg-neutral-200/70 dark:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $isAllFeed ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Semua Karya</span>
                </div>
                @if($isAllFeed)
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                @endif
            </a>

            <!-- Trending Tab -->
            <a href="{{ route('projects.index', ['tab' => 'trend']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $currentTab === 'trend' ? 'bg-neutral-200/70 dark:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'trend' ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    <span>Trending</span>
                </div>
                @if($currentTab === 'trend')
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                @endif
            </a>

            <!-- Terbaru Tab -->
            <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $currentTab === 'terbaru' ? 'bg-neutral-200/70 dark:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'terbaru' ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span>Terbaru</span>
                </div>
                @if($currentTab === 'terbaru')
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                @endif
            </a>

            <!-- Interactive Prototypes Tab (Violet Accent) -->
            <a href="{{ route('projects.index', ['tab' => 'prototype']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $currentTab === 'prototype' ? 'bg-purple-500/15 text-purple-600 dark:text-purple-300 font-semibold border border-purple-500/30' : 'text-neutral-600 dark:text-neutral-400 hover:text-purple-500 dark:hover:text-purple-300 hover:bg-purple-500/10' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'prototype' ? 'text-purple-500' : 'text-purple-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span>Prototipe Interaktif</span>
                </div>
                <span class="px-1.5 py-0.5 text-[9px] font-mono font-bold uppercase rounded bg-purple-500/20 text-purple-600 dark:text-purple-300 border border-purple-500/30">
                    Figma
                </span>
            </a>

            <!-- Top Upvoted Tab -->
            <a href="{{ route('projects.index', ['tab' => 'populer']) }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $currentTab === 'populer' ? 'bg-neutral-200/70 dark:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 {{ $currentTab === 'populer' ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Top Upvoted</span>
                </div>
                @if($currentTab === 'populer')
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                @endif
            </a>

            <!-- Koleksi Saya (Khusus Login) -->
            @auth
                @php $isBookmarks = request()->routeIs('projects.bookmarks'); @endphp
                <a href="{{ route('projects.bookmarks') }}" 
                   title="Koleksi Project Tersimpan"
                   class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition {{ $isBookmarks ? 'bg-neutral-200/70 dark:bg-white/[0.08] text-neutral-900 dark:text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ $isBookmarks ? 'text-orange-500' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span>Koleksi Tersimpan</span>
                    </div>
                    @if($isBookmarks)
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    @endif
                </a>
            @endauth
        </div>

        <!-- Section 2: Kategori Kodingan -->
        <div class="space-y-1 pt-2 border-t border-neutral-200/60 dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-2 py-1">
                <p class="text-[10px] font-mono uppercase font-bold tracking-wider text-neutral-400 dark:text-neutral-500">
                    Kategori Kodingan
                </p>
                @if(request('kategori'))
                    <a href="{{ route('projects.index') }}" class="text-[10px] text-orange-500 hover:underline">
                        Reset
                    </a>
                @endif
            </div>

            <div class="space-y-0.5 max-h-56 overflow-y-auto custom-scrollbar pr-1">
                @forelse($sidebarCategories ?? [] as $cat)
                    @php $isCatActive = request('kategori') === $cat->slug; @endphp
                    <a href="{{ route('projects.index', array_merge(request()->except('page'), ['kategori' => $cat->slug])) }}" 
                       class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $isCatActive ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400 font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.04]' }}">
                        <div class="flex items-center gap-2 truncate">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isCatActive ? 'bg-orange-500' : 'bg-neutral-300 dark:bg-neutral-700' }}"></span>
                            <span class="truncate">{{ $cat->name }}</span>
                        </div>
                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-neutral-100 dark:bg-white/[0.05] text-neutral-400">
                            {{ $cat->projects_count }}
                        </span>
                    </a>
                @empty
                    <p class="text-[11px] text-neutral-400 px-3 py-1">Memuat kategori...</p>
                @endforelse
            </div>
        </div>

        <!-- Section 3: Radar Tech Stacks -->
        <div class="space-y-2 pt-2 border-t border-neutral-200/60 dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-2">
                <p class="text-[10px] font-mono uppercase font-bold tracking-wider text-neutral-400 dark:text-neutral-500">
                    Tech Stacks
                </p>
                @if(request('tech'))
                    <a href="{{ route('projects.index') }}" class="text-[10px] text-orange-500 hover:underline">
                        Reset
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap gap-1 px-1">
                @php
                    $popularStacks = ['Laravel', 'Vue', 'React', 'Go', 'Python', 'Tailwind', 'Next.js', 'Flutter'];
                @endphp
                @foreach($popularStacks as $stack)
                    @php $isStackActive = request('tech') === $stack; @endphp
                    <a href="{{ route('projects.index', array_merge(request()->except('page'), ['tech' => $stack])) }}" 
                       class="px-2 py-0.5 rounded-md text-[10px] font-mono transition border {{ $isStackActive ? 'bg-neutral-900 text-white dark:bg-white dark:text-black border-transparent font-bold' : 'bg-neutral-100 dark:bg-white/[0.04] text-neutral-600 dark:text-neutral-400 border-neutral-200 dark:border-white/[0.06] hover:border-neutral-300 dark:hover:border-white/20' }}">
                        {{ $stack }}
                    </a>
                @endforeach
            </div>
        </div>

    </div>

    <!-- BOTTOM SECTION: USER ACCOUNT & THEME PREFERENCE DOCK -->
    <div class="pt-4 border-t border-neutral-200/80 dark:border-white/[0.08] space-y-3 mt-6">
        
        <!-- User Session Card -->
        @auth
            <div class="p-2.5 rounded-2xl bg-neutral-100/80 dark:bg-white/[0.04] border border-neutral-200 dark:border-white/[0.06] flex items-center justify-between gap-2.5">
                <a href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-2.5 min-w-0 group">
                    <img src="{{ Auth::user()->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode(Auth::user()->name) }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="w-8 h-8 rounded-xl bg-neutral-200 dark:bg-neutral-800 object-cover ring-1 ring-neutral-300 dark:ring-neutral-700 shrink-0 group-hover:scale-105 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-neutral-900 dark:text-white truncate leading-tight group-hover:text-orange-500 transition">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-[10px] text-neutral-400 font-mono truncate">
                            &#64;{{ Auth::user()->username }}
                        </p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" 
                            title="Keluar (Logout)"
                            class="p-1.5 rounded-lg text-neutral-400 hover:text-rose-500 hover:bg-rose-500/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <!-- Guest Callout Card -->
            <div class="p-3 rounded-2xl bg-neutral-100 dark:bg-white/[0.03] border border-neutral-200 dark:border-white/[0.06] space-y-2">
                <p class="text-[11px] font-medium text-neutral-600 dark:text-neutral-400 leading-snug">
                    Pamerkan karya kodingan dan bangun reputasimu di NampungYuk.
                </p>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="{{ route('login') }}" 
                       class="px-2.5 py-1.5 text-center text-xs font-semibold rounded-lg bg-neutral-200 dark:bg-white/10 hover:bg-neutral-300 dark:hover:bg-white/15 text-neutral-800 dark:text-neutral-200 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" 
                       class="btn-vercel-primary px-2.5 py-1.5 text-center text-xs font-semibold rounded-lg shadow-2xs transition">
                        Daftar
                    </a>
                </div>
            </div>
        @endauth

        <!-- Preferences Bar: Theme Toggle & Shortcuts Helper -->
        <div class="flex items-center justify-between text-xs text-neutral-500 px-1 pt-1">
            <button @click="$store.theme.toggle()" 
                    type="button" 
                    class="flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-white transition">
                <svg x-show="!$store.theme.dark" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="$store.theme.dark" x-cloak class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-[11px] font-medium" x-text="$store.theme.dark ? 'Mode Gelap' : 'Mode Terang'">Mode</span>
            </button>

            @auth
                <button @click="showHelpModal = true" 
                        type="button"
                        class="hover:text-orange-500 text-[11px] font-mono transition"
                        title="Shortcut Keyboard (?)">
                    Shortcuts (?)
                </button>
            @endauth
        </div>

    </div>

</div>
