<div class="h-full flex flex-col px-6 pb-3 select-none bg-[var(--surface-invert)] relative">
    <!-- Brand Header -->
    <div class="h-12 px-3 flex items-center shrink-0">
        <x-logo size="md" :show-tagline="false" />
    </div>

    <!-- Middle Nav Section (Scrollable on short screens) -->
    <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar pt-1 pb-2 flex flex-col gap-5 {{ auth()->check() ? 'justify-between' : '' }}">
        @php
            $isSearchNav = request()->routeIs('search.*');
            $isPeopleNav = request()->routeIs('projects.index') && request('view') === 'people';
            $isTrendNav = request()->routeIs('projects.index') && request('tab') === 'trend';
            $isTerbaruNav = request()->routeIs('projects.index') && request('tab') === 'terbaru';
            $isFollowingNav = request()->routeIs('projects.index') && request('tab') === 'following';
            $isExploreFeed = request()->routeIs('projects.index') && ! $isPeopleNav && ! $isTrendNav && ! $isTerbaruNav && ! $isFollowingNav && ! request('kategori') && ! request('tech') && ! request('tab');
        @endphp

        <!-- Group 1: Feed -->
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--text-muted)] shrink-0">
                Feed
            </p>

            <nav class="ny-nav flex flex-col gap-0.5">
                <!-- Semua Karya -->
                <a href="{{ route('projects.index') }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isExploreFeed ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="truncate">Semua Karya</span>
                    </div>
                </a>

                <!-- Trending -->
                <a href="{{ route('projects.index', ['tab' => 'trend']) }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isTrendNav ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span class="truncate">Trending</span>
                    </div>
                </a>

                <!-- Terbaru -->
                <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isTerbaruNav ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate">Terbaru</span>
                    </div>
                </a>

                @auth
                    <!-- Mengikuti -->
                    <a href="{{ route('projects.index', ['tab' => 'following']) }}"
                       class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isFollowingNav ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span class="truncate">Mengikuti</span>
                        </div>
                    </a>
                @endauth
            </nav>
        </div>

        <!-- Group 2: Jelajahi -->
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--text-muted)] shrink-0">
                Jelajahi
            </p>

            <nav class="ny-nav flex flex-col gap-0.5">
                <!-- Cari -->
                <a href="{{ route('search.index') }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isSearchNav ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="truncate">Cari</span>
                    </div>
                </a>

                <!-- Direktori Kreator -->
                <a href="{{ route('projects.index', ['view' => 'people']) }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $isPeopleNav ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="truncate">Direktori Kreator</span>
                    </div>
                </a>

                <!-- Komunitas -->
                <a href="{{ auth()->check() ? route('communities.index') : '#' }}"
                   @guest @click.prevent="window.openAuthModal('login')" @endguest
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('communities.*') ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="truncate">Komunitas</span>
                    </div>
                </a>

                <!-- Spaces -->
                <a href="{{ auth()->check() ? route('spaces.index') : '#' }}"
                   @guest @click.prevent="window.openAuthModal('login')" @endguest
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('spaces.*') ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                        <span class="truncate">Spaces</span>
                    </div>
                </a>
            </nav>
        </div>

        <!-- Group 3: Aktivitas -->
        <div>
            <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--text-muted)] shrink-0">
                Aktivitas
            </p>

            <nav class="ny-nav flex flex-col gap-0.5">
                <!-- Obrolan -->
                <a href="{{ auth()->check() ? route('chat.index') : '#' }}"
                   @guest @click.prevent="window.openAuthModal('login')" @endguest
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('chat.*') ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="truncate">Obrolan</span>
                    </div>
                </a>

                @auth
                    <!-- Permintaan Pengguna -->
                    <button type="button"
                            @click="requestsOpen = true"
                            class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium cursor-pointer">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate">Permintaan Pengguna</span>
                        </div>
                        @if(($chatRequests ?? collect())->count() > 0)
                            <span class="ml-auto text-[11px] font-mono px-1.5 py-0.5 rounded bg-[var(--surface-muted)] text-[var(--text-muted)]">
                                {{ $chatRequests->count() }}
                            </span>
                        @endif
                    </button>

                    @php $unreadCount = $unreadNotificationsCount ?? 0; @endphp
                    <!-- Notifikasi -->
                    <a href="{{ route('notifications.index') }}"
                       class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('notifications.*') ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="truncate">Notifikasi</span>
                        </div>
                        @if($unreadCount > 0)
                            <span class="px-1.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-500 text-[var(--primary-text)] font-mono leading-none tabular-nums shrink-0">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Koleksi Tersimpan -->
                    <a href="{{ route('projects.bookmarks') }}"
                       class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('projects.bookmarks') ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span class="truncate">Koleksi Tersimpan</span>
                        </div>
                    </a>
                @endauth
            </nav>
        </div>

        <!-- Conditional Admin Section -->
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->is_admin))
            @php $pendingCount = $pendingReportsCount ?? 0; @endphp
            <div class="ny-nav space-y-0.5 pt-3 border-t border-rose-500/20 shrink-0">
                <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-rose-500 flex items-center justify-between">
                    <span>Moderasi Admin</span>
                    @if($pendingCount > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </p>

                <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('admin.*') && request()->get('tab') === 'all_users' ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="truncate">Kelola Pengguna</span>
                    </div>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ request()->routeIs('admin.*') && request()->get('tab') !== 'all_users' ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="truncate">Laporan Pengaduan</span>
                    </div>
                    @if($pendingCount > 0)
                        <span class="px-1.5 py-0.5 text-[11px] font-bold rounded-full bg-rose-500 text-[var(--primary-text)] font-mono tabular-nums shrink-0">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endif

        <!-- Conditional Kategori Komunitas -->
        @php
            $sidebarComms = $sidebarCommunities ?? collect();
        @endphp
        @if(request()->routeIs('communities.*') && $sidebarComms->count() > 0)
            <div class="ny-nav space-y-0.5 pt-3 border-t border-[var(--border-color)] shrink-0">
                <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--text-muted)]">
                    Kategori Komunitas
                </p>
                @foreach($sidebarComms as $sc)
                    @php $scActive = request('kategori') === $sc->slug; @endphp
                    <a href="{{ route('communities.show', $sc->slug) }}"
                       class="flex items-center justify-between gap-2 h-11 px-3 rounded-lg text-[15px] transition border border-transparent {{ $scActive ? 'is-active relative bg-[var(--surface)] text-[var(--primary)] font-semibold before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-5 before:w-[2px] before:rounded-full before:bg-emerald-500' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium' }}">
                        <span class="truncate">{{ $sc->name }}</span>
                        <span class="text-[11px] font-mono tabular-nums px-1.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0">{{ $sc->members_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Primary CTA (auth only) -->
        @auth
            <div class="sticky bottom-0 z-10 bg-[var(--surface-invert)] pt-2 pb-3 border-t border-[var(--border-color)]">
                <a href="{{ route('projects.create') }}"
                   class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] font-bold h-12 px-4 text-[15px] transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Pamer Kodingan</span>
                </a>
            </div>
        @endauth
    </div>

    <!-- Bottom: guest auth actions & promo card -->
    @guest
        <div class="shrink-0 pt-3 flex flex-col gap-3">
            <!-- Promo Card -->
            <div class="rounded-xl bg-[var(--surface)] border border-[var(--border-color)] p-3.5 space-y-2">
                <h3 class="text-[14px] font-bold text-[var(--primary)] tracking-tight leading-tight">
                    Pamer karya, dapat feedback
                </h3>
                <p class="text-[12.5px] text-[var(--text-muted)] leading-snug">
                    Showcase project, kumpulkan upvote, dan dapat masukan teknis.
                </p>
                <div class="space-y-1.5">
                    <div class="flex items-start gap-2 text-[12.5px] text-[var(--text-muted)]">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Unggah project + link demo & GitHub</span>
                    </div>
                    <div class="flex items-start gap-2 text-[12.5px] text-[var(--text-muted)]">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Ngobrol langsung dengan sesama kreator</span>
                    </div>
                </div>
            </div>

            <!-- Auth Buttons -->
            <div class="space-y-2">
                <button type="button" @click="window.openAuthModal('register')"
                        class="w-full inline-flex items-center justify-center rounded-full bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] font-bold text-[14px] h-11 px-4 transition active:scale-[0.98] cursor-pointer">
                    <span>Daftar gratis</span>
                </button>
                <button type="button" @click="window.openAuthModal('login')"
                        class="w-full inline-flex items-center justify-center rounded-full border border-[var(--border-color)] hover:bg-[var(--surface-hover)] text-[var(--primary)] font-semibold text-[14px] h-11 px-4 transition active:scale-[0.98] cursor-pointer">
                    <span>Masuk</span>
                </button>
            </div>
        </div>
    @endguest

    <!-- Bottom: pinned user profile (authenticated only) -->
    @auth
        <div class="shrink-0 pt-2">
            <div class="relative" x-data="{ userMenuOpen: false }">
                <div x-show="userMenuOpen"
                     @click.outside="userMenuOpen = false"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute bottom-full left-0 right-0 mb-2 bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl py-1.5 z-50 overflow-hidden">
                    <div class="px-4 py-2 border-b border-[var(--border-color)]">
                        <p class="font-bold truncate text-[var(--primary)] text-sm">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-[var(--text-muted)] font-mono truncate mt-0.5">&#64;{{ Auth::user()->username }}</p>
                    </div>
                    <a href="{{ Auth::user()->username ? route('profile.show', Auth::user()->username) : '#' }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-[var(--primary)] hover:bg-[var(--surface-hover)] transition text-sm">
                        <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil Saya</span>
                    </a>
                    <div class="border-t border-[var(--border-color)] my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2.5 text-rose-400 hover:bg-rose-500/10 transition flex items-center gap-3 text-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Keluar dari &#64;{{ Auth::user()->username }}</span>
                        </button>
                    </form>
                </div>

                <button type="button"
                        @click="userMenuOpen = !userMenuOpen"
                        class="w-full flex items-center justify-between gap-2.5 px-3 py-2 rounded-lg border border-transparent hover:border-[var(--border-color)] hover:bg-[var(--surface)] transition text-left cursor-pointer group">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <x-user-avatar :user="Auth::user()" size="sm" class="shrink-0" />
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-[var(--primary)] truncate leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ Auth::user()->username }}</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-[var(--text-muted)] group-hover:text-[var(--primary)] shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </button>
            </div>
        </div>
    @endauth
</div>
