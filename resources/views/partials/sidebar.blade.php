@php
    $compact = (bool) ($compact ?? false);
@endphp

<div class="h-full flex flex-col {{ $compact ? 'px-2 items-center' : 'px-4' }} pb-3 select-none bg-[var(--surface-invert)] relative">
    <!-- Brand Header -->
    <div class="h-[65px] {{ $compact ? 'w-full justify-center' : 'px-3' }} mb-1 flex items-center shrink-0">
        <x-logo size="md" :show-text="! $compact" :show-tagline="false" />
    </div>

    <!-- Middle Nav Section (Scrollable on short screens) -->
    <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar pt-2.5 pb-1 flex flex-col gap-3.5 {{ $compact ? 'w-full items-center' : '' }}">
        @php
            $isSearchNav = request()->routeIs('search.*');
            $isPeopleNav = request()->routeIs('projects.index') && request('view') === 'people';
            $isTrendNav = request()->routeIs('projects.index') && request('tab') === 'trend';
            $isTerbaruNav = request()->routeIs('projects.index') && request('tab') === 'terbaru';
            $isExploreFeed = request()->routeIs('projects.index') && ! $isPeopleNav && ! $isTrendNav && ! $isTerbaruNav && ! request('kategori') && ! request('tech') && ! request('tab');
            $isAdminUsers = request()->routeIs('admin.*') && request()->get('tab') === 'all_users';
            $isAdminReports = request()->routeIs('admin.*') && request()->get('tab') !== 'all_users';
            $isCommunitiesNav = request()->routeIs('communities.*');
            $isSpacesNav = request()->routeIs('spaces.*');
            $isChatNav = request()->routeIs('chat.*');
            $isNotifNav = request()->routeIs('notifications.*');
            $isBookmarksNav = request()->routeIs('projects.bookmarks');
            $unreadChat = $unreadMessagesCount ?? 0;
            $unreadCount = $unreadNotificationsCount ?? 0;
            $pendingCount = $pendingReportsCount ?? 0;

            $navClass = static function (bool $active) use ($compact): string {
                if ($compact) {
                    return 'group relative flex items-center justify-center w-11 h-11 mx-auto rounded-xl border transition-colors '
                        . ($active
                            ? 'is-active bg-[var(--surface)] border-[var(--border-color)] text-[var(--primary)]'
                            : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/70');
                }

                return 'group flex items-center justify-between gap-2 h-11 px-3 rounded-xl text-[15px] border transition-colors '
                    . ($active
                        ? 'is-active bg-[var(--surface)] border-[var(--border-color)] text-[var(--primary)] font-semibold'
                        : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/70 font-medium');
            };

            $iconClass = static fn (bool $active): string => 'w-5 h-5 shrink-0 transition-colors '
                . ($active ? 'text-emerald-400' : 'text-[var(--text-faint)] group-hover:text-[var(--primary)]');

            $groupLabelClass = 'px-3 mb-1.5 font-mono text-[10.5px] font-medium uppercase tracking-[0.12em] text-[var(--text-faint)] shrink-0';
            $compactBadgeClass = 'absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold rounded-full bg-emerald-500 text-[var(--primary-text)] font-mono leading-none tabular-nums flex items-center justify-center ring-2 ring-[var(--surface-invert)]';
        @endphp

        <!-- Group 1: Feed -->
        <div class="{{ $compact ? 'w-full' : '' }}">
            @if($compact)
                <p class="{{ $groupLabelClass }} invisible select-none overflow-hidden whitespace-nowrap" aria-hidden="true">&nbsp;</p>
            @else
                <p class="{{ $groupLabelClass }}">
                    Feed
                </p>
            @endif

            <nav class="ny-nav flex flex-col gap-0.5 {{ $compact ? 'items-center' : '' }}">
                <!-- Semua Karya -->
                <a href="{{ route('projects.index') }}"
                   title="Semua Karya"
                   aria-label="Semua Karya"
                   class="{{ $navClass($isExploreFeed) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isExploreFeed) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isExploreFeed) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span class="truncate">Semua Karya</span>
                        </div>
                        @if($isExploreFeed)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>

                <!-- Trending -->
                <a href="{{ route('projects.index', ['tab' => 'trend']) }}"
                   title="Trending"
                   aria-label="Trending"
                   class="{{ $navClass($isTrendNav) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isTrendNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isTrendNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span class="truncate">Trending</span>
                        </div>
                        @if($isTrendNav)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>

                <!-- Terbaru -->
                <a href="{{ route('projects.index', ['tab' => 'terbaru']) }}"
                   title="Terbaru"
                   aria-label="Terbaru"
                   class="{{ $navClass($isTerbaruNav) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isTerbaruNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isTerbaruNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Terbaru</span>
                        </div>
                        @if($isTerbaruNav)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>
            </nav>
        </div>

        <!-- Group 2: Jelajahi -->
        <div class="{{ $compact ? 'w-full ' : '' }}pt-3 border-t border-[var(--border-color)]/45">
            @if($compact)
                <p class="{{ $groupLabelClass }} invisible select-none overflow-hidden whitespace-nowrap" aria-hidden="true">&nbsp;</p>
            @else
                <p class="{{ $groupLabelClass }}">
                    Jelajahi
                </p>
            @endif

            <nav class="ny-nav flex flex-col gap-0.5 {{ $compact ? 'items-center' : '' }}">
                @auth
                    <!-- Cari (desktop guest sudah punya search bar di rail kanan & mobile header) -->
                    <a href="{{ route('search.index') }}"
                       title="Cari"
                       aria-label="Cari"
                       class="{{ $navClass($isSearchNav) }}">
                        @if($compact)
                            <svg class="{{ $iconClass($isSearchNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        @else
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="{{ $iconClass($isSearchNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span class="truncate">Cari</span>
                            </div>
                            @if($isSearchNav)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                            @endif
                        @endif
                    </a>
                @endauth

                <!-- Direktori Kreator -->
                <a href="{{ route('projects.index', ['view' => 'people']) }}"
                   title="Direktori Kreator"
                   aria-label="Direktori Kreator"
                   class="{{ $navClass($isPeopleNav) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isPeopleNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isPeopleNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate">Direktori Kreator</span>
                        </div>
                        @if($isPeopleNav)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>

                <!-- Komunitas -->
                <a href="{{ auth()->check() ? route('communities.index') : '#' }}"
                   @guest @click.prevent="window.openAuthModal('login')" @endguest
                   title="Komunitas"
                   aria-label="Komunitas"
                   class="{{ $navClass($isCommunitiesNav) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isCommunitiesNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isCommunitiesNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="truncate">Komunitas</span>
                        </div>
                        @if($isCommunitiesNav)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>

                <!-- Spaces -->
                <a href="{{ auth()->check() ? route('spaces.index') : '#' }}"
                   @guest @click.prevent="window.openAuthModal('login')" @endguest
                   title="Spaces"
                   aria-label="Spaces"
                   class="{{ $navClass($isSpacesNav) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isSpacesNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isSpacesNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                            </svg>
                            <span class="truncate">Spaces</span>
                        </div>
                        @if($isSpacesNav)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                        @endif
                    @endif
                </a>
            </nav>
        </div>

        {{-- Group 3: Aktivitas (auth only — menghindari dead-end & hemat ruang untuk guest) --}}
        @auth
            <div class="{{ $compact ? 'w-full ' : '' }}pt-3 border-t border-[var(--border-color)]/45">
                @if($compact)
                    <p class="{{ $groupLabelClass }} invisible select-none overflow-hidden whitespace-nowrap" aria-hidden="true">&nbsp;</p>
                @else
                    <p class="{{ $groupLabelClass }}">
                        Aktivitas
                    </p>
                @endif

                <nav class="ny-nav flex flex-col gap-0.5 {{ $compact ? 'items-center' : '' }}">
                    <!-- Obrolan -->
                    <a href="{{ route('chat.index') }}"
                       title="Obrolan"
                       aria-label="Obrolan"
                       class="{{ $navClass($isChatNav) }}">
                        @if($compact)
                            <svg class="{{ $iconClass($isChatNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span x-show="unreadChatCount > 0"
                                  x-cloak
                                  x-text="unreadChatCount > 9 ? '9+' : unreadChatCount"
                                  class="{{ $compactBadgeClass }}">
                                {{ $unreadChat > 9 ? '9+' : $unreadChat }}
                            </span>
                        @else
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="{{ $iconClass($isChatNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="truncate">Obrolan</span>
                            </div>
                            <span x-show="unreadChatCount > 0"
                                  x-cloak
                                  x-text="unreadChatCount > 99 ? '99+' : unreadChatCount"
                                  class="px-1.5 py-0.5 text-[10.5px] font-bold rounded-full bg-emerald-500 text-[var(--primary-text)] font-mono leading-none tabular-nums shrink-0">
                                {{ $unreadChat > 99 ? '99+' : $unreadChat }}
                            </span>
                            @if($isChatNav)
                                <span x-show="unreadChatCount === 0" class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                            @endif
                        @endif
                    </a>

                    <!-- Permintaan Pengguna -->
                    <button type="button"
                            @click="requestsOpen = true"
                            title="Permintaan Pengguna"
                            aria-label="Permintaan Pengguna"
                            class="{{ $navClass(false) }} cursor-pointer">
                        @if($compact)
                            <svg class="{{ $iconClass(false) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="chatRequestsCount > 0"
                                  x-cloak
                                  x-text="chatRequestsCount > 9 ? '9+' : chatRequestsCount"
                                  class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold rounded-full bg-amber-400 text-[#0B0B0D] font-mono leading-none tabular-nums flex items-center justify-center ring-2 ring-[var(--surface-invert)]">
                                {{ ($chatRequests ?? collect())->count() }}
                            </span>
                        @else
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="{{ $iconClass(false) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">Permintaan Pengguna</span>
                            </div>
                            <span x-show="chatRequestsCount > 0"
                                  x-cloak
                                  x-text="chatRequestsCount"
                                  class="ml-auto text-[11px] font-mono tabular-nums px-1.5 py-0.5 rounded-md bg-[var(--surface)] border border-[var(--border-color)] text-[var(--primary)] leading-none">
                                {{ ($chatRequests ?? collect())->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Notifikasi -->
                    <a href="{{ route('notifications.index') }}"
                       title="Notifikasi"
                       @if($compact) aria-label="Notifikasi" @endif
                       class="{{ $navClass($isNotifNav) }}">
                        @if($compact)
                            <svg class="{{ $iconClass($isNotifNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCount > 0)
                                <span class="{{ $compactBadgeClass }}">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        @else
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="{{ $iconClass($isNotifNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="truncate">Notifikasi</span>
                            </div>
                            @if($unreadCount > 0)
                                <span class="px-1.5 py-0.5 text-[10.5px] font-bold rounded-full bg-emerald-500 text-[var(--primary-text)] font-mono leading-none tabular-nums shrink-0">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @elseif($isNotifNav)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                            @endif
                        @endif
                    </a>

                    <!-- Koleksi Tersimpan -->
                    <a href="{{ route('projects.bookmarks') }}"
                       title="Koleksi Tersimpan"
                       aria-label="Koleksi Tersimpan"
                       class="{{ $navClass($isBookmarksNav) }}">
                        @if($compact)
                            <svg class="{{ $iconClass($isBookmarksNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                        @else
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="{{ $iconClass($isBookmarksNav) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <span class="truncate">Koleksi Tersimpan</span>
                            </div>
                            @if($isBookmarksNav)
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" aria-hidden="true"></span>
                            @endif
                        @endif
                    </a>
                </nav>
            </div>
        @endauth

        <!-- Conditional Admin Section -->
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->is_admin))
            <div class="ny-nav {{ $compact ? 'w-full flex flex-col items-center gap-0.5' : 'space-y-0.5' }} pt-3 border-t border-rose-500/20 shrink-0">
                @if($compact)
                    <p class="{{ $groupLabelClass }} invisible select-none overflow-hidden whitespace-nowrap" aria-hidden="true">&nbsp;</p>
                @else
                    <p class="px-3 mb-1.5 font-mono text-[10.5px] font-medium uppercase tracking-[0.12em] text-rose-400 flex items-center justify-between">
                        <span>Moderasi Admin</span>
                        @if($pendingCount > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        @endif
                    </p>
                @endif

                <a href="{{ route('admin.reports.index', ['tab' => 'all_users']) }}"
                   title="Kelola Pengguna"
                   aria-label="Kelola Pengguna"
                   class="{{ $navClass($isAdminUsers) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isAdminUsers) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isAdminUsers) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="truncate">Kelola Pengguna</span>
                        </div>
                    @endif
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   title="Laporan Pengaduan"
                   aria-label="Laporan Pengaduan"
                   class="{{ $navClass($isAdminReports) }}">
                    @if($compact)
                        <svg class="{{ $iconClass($isAdminReports) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        @if($pendingCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold rounded-full bg-rose-500 text-white font-mono leading-none tabular-nums flex items-center justify-center ring-2 ring-[var(--surface-invert)]">
                                {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                            </span>
                        @endif
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="{{ $iconClass($isAdminReports) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="truncate">Laporan Pengaduan</span>
                        </div>
                        @if($pendingCount > 0)
                            <span class="px-1.5 py-0.5 text-[10.5px] font-bold rounded-full bg-rose-500 text-[var(--primary-text)] font-mono tabular-nums shrink-0 leading-none">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    @endif
                </a>
            </div>
        @endif

        <!-- Conditional Kategori Komunitas -->
        @php
            $sidebarComms = $sidebarCommunities ?? collect();
        @endphp
        @if(! $compact && request()->routeIs('communities.*') && $sidebarComms->count() > 0)
            <div class="ny-nav space-y-0.5 pt-3 border-t border-[var(--border-color)] shrink-0">
                <p class="{{ $groupLabelClass }}">
                    Kategori Komunitas
                </p>
                @foreach($sidebarComms as $sc)
                    @php $scActive = request('kategori') === $sc->slug; @endphp
                    <a href="{{ route('communities.show', $sc->slug) }}" class="{{ $navClass($scActive) }}">
                        <span class="truncate">{{ $sc->name }}</span>
                        <span class="text-[11px] font-mono tabular-nums px-1.5 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0 leading-none">{{ $sc->members_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Primary CTA (auth only) -->
        @auth
            <div class="mt-auto sticky bottom-0 z-10 bg-[var(--surface-invert)] pt-2.5 pb-2 border-t border-[var(--border-color)]/70 {{ $compact ? 'w-full flex justify-center' : '' }}">
                @if($compact)
                    <a href="{{ route('projects.create') }}"
                       title="Pamer Kodingan"
                       aria-label="Pamer Kodingan"
                       class="w-12 h-12 inline-flex items-center justify-center rounded-full bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] transition active:scale-95 cursor-pointer shadow-xs">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </a>
                @else
                    <a href="{{ route('projects.create') }}"
                       class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] font-bold h-12 px-4 text-[15px] transition active:scale-[0.98] cursor-pointer shadow-xs">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Pamer Kodingan</span>
                    </a>
                @endif
            </div>
        @endauth

        <!-- Guest auth actions & promo card (menyambung langsung di bawah nav, tidak mentok ke dasar layar) -->
        @guest
            @unless($compact)
                <div class="shrink-0 pt-4 pb-2 border-t border-[var(--border-color)]/45 flex flex-col gap-3">
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
            @endunless
        @endguest
    </div>

    <!-- Bottom: pinned user profile (authenticated only) -->
    @auth
        <div class="shrink-0 pt-2 {{ $compact ? 'w-full flex justify-center' : '' }}">
            <div class="relative w-full" x-data="{ userMenuOpen: false }">
                <div x-show="userMenuOpen"
                     @click.outside="userMenuOpen = false"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute {{ $compact ? 'bottom-0 left-full ml-2.5 w-56' : 'bottom-full left-0 right-0 mb-2' }} bg-[var(--surface)] border border-[var(--border-strong)] rounded-2xl shadow-2xl p-1.5 z-50 overflow-hidden">
                    <div class="px-3 py-2 mb-1 border-b border-[var(--border-color)]">
                        <p class="font-semibold truncate text-[var(--primary)] text-[13.5px]">{{ Auth::user()->name }}</p>
                        <p class="text-[11.5px] text-[var(--text-muted)] font-mono truncate mt-0.5">&#64;{{ Auth::user()->username }}</p>
                    </div>
                    <a href="{{ Auth::user()->username ? route('profile.show', Auth::user()->username) : '#' }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--primary)] hover:bg-[var(--surface-hover)] transition text-[13.5px] font-medium">
                        <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('settings.index') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[var(--primary)] hover:bg-[var(--surface-hover)] transition text-[13.5px] font-medium">
                        <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Pengaturan</span>
                    </a>
                    <div class="border-t border-[var(--border-color)] my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 rounded-xl text-rose-400 hover:bg-rose-500/10 transition flex items-center gap-2.5 text-[13.5px] font-medium cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Keluar dari &#64;{{ Auth::user()->username }}</span>
                        </button>
                    </form>
                </div>

                @if($compact)
                    <button type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            title="{{ Auth::user()->name }} (@{{ Auth::user()->username }})"
                            aria-label="Menu akun {{ Auth::user()->name }}"
                            class="w-full py-2.5 rounded-xl flex items-center justify-center border border-[var(--border-color)]/80 bg-[var(--surface)]/70 hover:bg-[var(--surface)] hover:border-[var(--border-strong)] transition cursor-pointer">
                        <x-user-avatar :user="Auth::user()" size="md" class="shrink-0" />
                    </button>
                @else
                    <button type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl border border-[var(--border-color)]/80 bg-[var(--surface)]/70 hover:bg-[var(--surface)] hover:border-[var(--border-strong)] transition text-left cursor-pointer group">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-user-avatar :user="Auth::user()" size="md" class="shrink-0" />
                            <div class="min-w-0">
                                <p class="text-[14px] font-semibold text-[var(--primary)] truncate leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[11.5px] font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ Auth::user()->username }}</p>
                            </div>
                        </div>
                        <svg class="w-[18px] h-[18px] text-[var(--text-faint)] group-hover:text-[var(--primary)] shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endauth
</div>
