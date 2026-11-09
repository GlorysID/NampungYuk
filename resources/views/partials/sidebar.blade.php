<div class="h-full flex flex-col justify-between overflow-hidden p-4 xl:p-5 select-none bg-[var(--surface-invert)]">
    <!-- Brand Header -->
    <div class="flex items-center justify-between gap-3 px-1 pt-0.5 pb-3 shrink-0">
        <x-logo size="md" :show-tagline="true" />
        <span class="text-[11px] font-mono tabular-nums px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
            v1.0
        </span>
    </div>

    <!-- Middle Section -->
    <div class="flex-1 space-y-4 pr-1 my-1">
        <!-- CTA: Primary Action -->
        @auth
            <a href="{{ route('projects.create') }}"
               class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] hover:opacity-90 font-bold h-11 text-[15px] transition shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Pamer Kodingan</span>
            </a>
        @else
            <div class="grid grid-cols-2 gap-2 lg:hidden">
                <button type="button" @click="window.openAuthModal('login')"
                        class="w-full inline-flex items-center justify-center rounded-xl border border-[var(--border-color)] hover:bg-[var(--surface-hover)] text-[var(--primary)] text-sm font-semibold h-10 px-3 transition cursor-pointer">
                    <span>Masuk</span>
                </button>
                <button type="button" @click="window.openAuthModal('register')"
                        class="w-full inline-flex items-center justify-center rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] text-sm font-bold h-10 px-3 transition shadow-xs cursor-pointer">
                    <span>Daftar</span>
                </button>
            </div>
        @endauth

        <!-- Section 1: Menu Utama -->
        <div class="space-y-1">
            <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--text-faint)] mb-1.5">
                Menu Utama
            </p>

            @php
                $isPeopleNav = request()->routeIs('projects.index') && request('view') === 'people';
                $isTrendNav = request()->routeIs('projects.index') && request('tab') === 'trend';
                $isProtoNav = request()->routeIs('projects.index') && request('tab') === 'prototype';
                $isTerbaruNav = request()->routeIs('projects.index') && request('tab') === 'terbaru';
                $isFollowingNav = request()->routeIs('projects.index') && request('tab') === 'following';
                $isExploreFeed = request()->routeIs('projects.index') && ! $isPeopleNav && ! $isTrendNav && ! $isProtoNav && ! request('kategori') && ! request('tech') && ! request('tab');
            @endphp

            <!-- Semua Karya -->
            <a href="{{ route('projects.index') }}" 
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isExploreFeed ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Semua Karya</span>
                </div>
                @if($isExploreFeed)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <!-- Trending -->
            <a href="{{ route('projects.index', ['tab' => 'trend']) }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isTrendNav ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>Trending</span>
                </div>
                @if($isTrendNav)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <!-- Prototipe Interaktif -->
            <a href="{{ route('projects.index', ['tab' => 'prototype']) }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isProtoNav ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Prototipe Interaktif</span>
                </div>
                @if($isProtoNav)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <!-- Terbaru -->
            <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isTerbaruNav ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Terbaru</span>
                </div>
                @if($isTerbaruNav)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            @auth
                <!-- Mengikuti -->
                <a href="{{ route('projects.index', ['tab' => 'following']) }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isFollowingNav ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span>Mengikuti</span>
                    </div>
                    @if($isFollowingNav)
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @endif
                </a>
            @endauth

            <!-- Komunitas -->
            <a href="{{ route('communities.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ request()->routeIs('communities.*') ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Komunitas</span>
                </div>
                @if(request()->routeIs('communities.*'))
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <!-- Spaces -->
            <a href="{{ route('spaces.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ request()->routeIs('spaces.*') ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    <span>Spaces</span>
                </div>
                @if(request()->routeIs('spaces.*'))
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            <!-- Direktori Kreator -->
            <a href="{{ route('projects.index', ['view' => 'people']) }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isPeopleNav ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Direktori Kreator</span>
                </div>
                @if($isPeopleNav)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>

            @auth
                <!-- Obrolan -->
                <a href="{{ route('chat.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ request()->routeIs('chat.*') ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span>Obrolan</span>
                    </div>
                    @if(request()->routeIs('chat.*'))
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <!-- Koleksi Tersimpan -->
                <a href="{{ route('projects.bookmarks') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ request()->routeIs('projects.bookmarks') ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span>Koleksi Tersimpan</span>
                    </div>
                    @if(request()->routeIs('projects.bookmarks'))
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <!-- Profil Saya -->
                @php $isMyProfile = request()->routeIs('profile.show') && request()->route('username') === auth()->user()->username; @endphp
                <a href="{{ route('profile.show', auth()->user()->username) }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] transition {{ $isMyProfile ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil Saya</span>
                    </div>
                    @if($isMyProfile)
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @endif
                </a>
            @endauth
        </div>

        <!-- Section 3: Admin Moderasi Laporan -->
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->is_admin))
            @php $pendingCount = $pendingReportsCount ?? 0; @endphp
            <div class="space-y-1 pt-2.5 border-t border-rose-500/20">
                <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-rose-500 flex items-center justify-between mb-1.5">
                    <span>Moderasi Admin</span>
                    @if($pendingCount > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </p>

                <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] font-medium transition {{ request()->routeIs('admin.*') && request()->get('tab') === 'all_users' ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 ' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Kelola Pengguna</span>
                    </div>
                    @if(request()->routeIs('admin.*') && request()->get('tab') === 'all_users')
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[15px] font-medium transition {{ request()->routeIs('admin.*') && request()->get('tab') !== 'all_users' ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border-l-2 border-[var(--primary)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 ' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Laporan Pengaduan</span>
                    </div>
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 text-xs font-bold rounded-md bg-rose-500 text-white font-mono">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endif

        <!-- Section 4: Kategori Kodingan -->
        @php
            $sidebarComms = $sidebarCommunities ?? collect();
            $sidebarCats = (isset($categories) && count($categories) > 0) ? $categories : ($sidebarCategories ?? collect());
        @endphp
        @if(request()->routeIs('communities.*') && $sidebarComms->count() > 0)
            <div class="space-y-1 pt-2.5 border-t border-[var(--border-color)]">
                <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--text-faint)] mb-1.5">
                    Kategori Komunitas
                </p>
                @foreach($sidebarComms as $sc)
                    @php $scActive = request('kategori') === $sc->slug; @endphp
                    <a href="{{ route('communities.show', $sc->slug) }}"
                       class="flex items-center justify-between px-3 py-2 text-[14px] rounded-lg transition {{ $scActive ? 'text-[var(--primary)] font-semibold bg-[var(--surface-hover)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 ' }}">
                        <span class="truncate">{{ $sc->name }}</span>
                        <span class="text-xs font-mono tabular-nums px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">{{ $sc->members_count }}</span>
                    </a>
                @endforeach
            </div>
        @elseif($sidebarCats->count() > 0)
            @php
                $activeCatSlug = request('kategori');
                $topCategories = $sidebarCats->sortByDesc('projects_count')->take(5);
            @endphp
            <div class="space-y-1 pt-2.5 border-t border-[var(--border-color)]">
                <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--text-faint)] mb-1.5">
                    Kategori Kodingan
                </p>
                @foreach($topCategories as $category)
                    @php $isCatActive = ($activeCatSlug === $category->slug); @endphp
                    <a href="{{ route('projects.index', ['kategori' => $category->slug]) }}"
                       class="flex items-center justify-between px-3 py-2 text-[14px] rounded-lg transition {{ $isCatActive ? 'text-[var(--primary)] font-semibold bg-[var(--surface-hover)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 ' }}">
                        <span class="truncate">{{ $category->name }}</span>
                        <span class="text-xs font-mono tabular-nums px-2 py-0.5 rounded-md {{ $isCatActive ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]' }}">{{ $category->projects_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Section 5: Tech Stacks -->
        <div class="space-y-2 pt-2.5 border-t border-[var(--border-color)]">
            <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--text-faint)] mb-1.5">
                Tech Stacks
            </p>
            <div class="flex flex-wrap gap-1.5 px-1">
                @foreach(['Laravel', 'Vue', 'React', 'TailwindCSS', 'Go', 'AI'] as $stack)
                    <a href="{{ route('projects.index', ['tech' => $stack]) }}" class="inline-flex items-center gap-0.5 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-[var(--surface-muted)] text-[var(--text-soft)] border border-[var(--border-color)] hover:text-[var(--primary)] hover:border-[var(--border-strong)] transition">
                        #{{ $stack }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>