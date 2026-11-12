<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/icon-dark.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-dark.png') }}">

    <title>@yield('title', 'NampungYuk — Platform Showcase Project Programmer')</title>
    <meta name="description" content="@yield('meta_description', 'Platform community untuk berbagi project web, software, coding, dan karya digital. Temukan inspirasi, tantangan, dan apa yang dipelajari developer.')">

    <!-- Fonts: Geist + Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html { background-color: var(--bg-app); color-scheme: dark; }
        [x-cloak] { display: none !important; }

        /*
         * Critical shell geometry. Parsed with the HTML (app.css may still be in
         * flight), so the 3-column shell can never render collapsed and snap into
         * place on navigation. Kept after the vite tags on purpose: same
         * specificity, so later-in-document wins where both apply. Does not set
         * main max-width, because pages override the main_width section.
         *
         * shortcut: these values duplicate the Tailwind classes on the shell
         * elements in this same file; change both together if a column resizes.
         */
        .ny-shell { display: flex; justify-content: flex-start; width: 100%; max-width: 1440px; margin-inline: auto; }
        .ny-main { flex: 1 1 0%; min-width: 0; }
        /* Default-hidden so the columns never render in-flow before app.css lands. */
        .ny-aside { display: none; }
        .ny-rail { display: none; }
        @media (min-width: 1024px) { .ny-aside { display: flex; flex-direction: column; flex: 0 0 264px; } }
        @media (min-width: 1280px) {
            .ny-aside { flex-basis: 280px; }
            .ny-rail { display: block; flex: 0 0 370px; }
        }
    </style>
</head>
<body class="bg-[var(--bg-app)] text-[var(--primary)] min-h-screen transition-colors duration-150 antialiased selection:bg-[var(--primary)] selection:text-[var(--primary-text)] relative overflow-x-hidden pb-20 lg:pb-0"
      x-data="{
          mobileSidebarOpen: false,
          mobileSearchOpen: false,
          activeCardIndex: -1,
          showHelpModal: false,
          toast: { show: false, message: '' },
          init() {
              window.notify = (msg) => this.notify(msg);
          },
          notify(msg) {
              this.toast.message = msg;
              this.toast.show = true;
              setTimeout(() => { this.toast.show = false; }, 2600);
          },
          handleKeydown(e) {
              if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                  if (e.key === 'Escape') {
                      document.activeElement.blur();
                      this.mobileSearchOpen = false;
                  }
                  return;
              }
              if (e.key === '/' || (e.metaKey && e.key === 'k') || (e.ctrlKey && e.key === 'k')) {
                  const searchEl = document.getElementById('nav-search');
                  const mobileSearchEl = document.getElementById('nav-search-mobile');
                  if (!searchEl && !mobileSearchEl) return;
                  e.preventDefault();
                  if (window.innerWidth < 1280) {
                      this.mobileSearchOpen = true;
                      this.$nextTick(() => mobileSearchEl?.focus());
                  } else {
                      searchEl?.focus();
                  }
                  return;
              }
              if (e.key === '?') {
                  e.preventDefault();
                  this.showHelpModal = !this.showHelpModal;
                  return;
              }
              if (e.key === 'Escape') {
                  this.showHelpModal = false;
                  this.mobileSidebarOpen = false;
                  this.mobileSearchOpen = false;
                  return;
              }
              const cards = Array.from(document.querySelectorAll('.project-nav-card'));
              if (!cards.length) return;

              if (e.key === 'j' || e.key === 'ArrowDown') {
                  e.preventDefault();
                  this.activeCardIndex = Math.min(this.activeCardIndex + 1, cards.length - 1);
                  this.focusCard(cards);
              } else if (e.key === 'k' || e.key === 'ArrowUp') {
                  e.preventDefault();
                  this.activeCardIndex = Math.max(this.activeCardIndex - 1, 0);
                  this.focusCard(cards);
              } else if (e.key === 'Enter' && this.activeCardIndex >= 0) {
                  const link = cards[this.activeCardIndex]?.querySelector('.card-detail-link');
                  if (link) link.click();
              } else if (e.key === 'u' && this.activeCardIndex >= 0) {
                  const btn = cards[this.activeCardIndex]?.querySelector('.btn-upvote');
                  if (btn) btn.click();
              } else if (e.key === 'b' && this.activeCardIndex >= 0) {
                  const btn = cards[this.activeCardIndex]?.querySelector('.btn-bookmark');
                  if (btn) btn.click();
              }
          },
          focusCard(cards) {
              cards.forEach((c, idx) => {
                  if (idx === this.activeCardIndex) {
                      c.classList.add('keyboard-active-card');
                      c.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  } else {
                      c.classList.remove('keyboard-active-card');
                  }
              });
          }
      }"
      @keydown.window="handleKeydown($event)">

    <!-- MOBILE SIDEBAR BACKDROP & DRAWER -->
    <div x-show="mobileSidebarOpen" 
         x-cloak 
         class="fixed inset-0 z-50 lg:hidden"
         role="dialog" 
         aria-modal="true"
         aria-label="Menu Navigasi Mobile">
        <!-- Backdrop -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

        <!-- Off-canvas Mobile Menu -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-in-out duration-250 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-250 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex-1 flex flex-col max-w-xs w-full h-full bg-[var(--surface-invert)] border-r border-[var(--border-color)] z-10">
            
            <!-- Close Button -->
            <div class="absolute top-3 right-3 z-20">
                <button @click="mobileSidebarOpen = false" 
                        type="button"
                        class="p-1.5 rounded-md text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition"
                        aria-label="Tutup Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @include('partials.sidebar')
        </div>
    </div>

    @php 
        $onCommunity = request()->routeIs('communities.*'); 
        $profileUser = request()->routeIs('profile.show') ? request()->route('username') : null;
        $onProfile = $profileUser !== null;
        $onPeople = request()->routeIs('projects.index') && request('view') === 'people';
        $searchAction = $onCommunity ? route('communities.index') : ($onProfile ? route('projects.index') : url('/cari'));
    @endphp

    <!-- MAIN 3-COLUMN SHELL -->
    <div class="ny-shell mx-auto flex w-full @yield('shell_width', 'max-w-[1440px]') min-h-screen">
        
        <!-- DESKTOP SIDEBAR -->
        <aside class="ny-aside hidden lg:flex lg:flex-col w-[264px] xl:w-[280px] shrink-0 sticky top-0 h-screen z-30 bg-[var(--surface-invert)] border-r border-[var(--border-color)]">
            @include('partials.sidebar')
        </aside>

        <!-- CENTER COLUMN -->
        <main class="ny-main flex-1 min-w-0 w-full @yield('main_width','max-w-[760px]') lg:border-x border-[var(--border-color)] flex flex-col">

            @hasSection('header')
                @yield('header')
            @else
            <!-- FEED HEADER (64px h-16, sticky, translucent) -->
            <header class="sticky top-0 z-20 h-16 px-4 sm:px-6 flex items-center justify-between gap-3 relative bg-[var(--bg-app)]/85 backdrop-blur-md border-b border-[var(--border-color)]">
                
                <!-- MOBILE SEARCH OVERLAY (Full-width on Mobile when opened) -->
                <div x-show="mobileSearchOpen" 
                     x-cloak 
                     @keydown.escape.window="mobileSearchOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-[0.99]"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-[0.99]"
                     class="absolute inset-0 z-30 px-3 flex items-center bg-[var(--bg-app)]/95 backdrop-blur-md rounded-none lg:rounded-2xl gap-2 border-b lg:border border-[var(--border-color)]">
                    <form action="{{ $searchAction }}" method="GET" class="flex-1 relative flex items-center">
                        @if($onProfile)
                            <input type="hidden" name="u" value="{{ $profileUser }}">
                        @elseif(! $onCommunity && $onPeople)
                            <input type="hidden" name="tab" value="orang">
                        @endif
                        <svg class="w-4 h-4 text-[var(--text-muted)] absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" 
                                id="nav-search-mobile"
                                name="q" 
                                value="{{ $onProfile ? '' : request('q') }}"
                                placeholder="{{ $onProfile ? 'Cari karya @'.$profileUser.'...' : ($onCommunity ? 'Cari komunitas...' : 'Cari karya, akun, atau komunitas...') }}" 
                                aria-label="{{ $onProfile ? 'Cari karya '.$profileUser : ($onCommunity ? 'Cari komunitas' : 'Cari karya, akun, atau komunitas') }}"
                                class="w-full pl-9 pr-8 py-2 text-sm bg-[var(--surface-muted-flat)] border border-[var(--border-color)] rounded-lg text-[var(--primary)] placeholder-[var(--text-muted)] outline-none focus:border-[var(--primary)]">
                        @if(request('q') && ! $onProfile)
                            <a href="{{ $onCommunity ? route('communities.index') : ($onPeople ? route('search.index', ['tab' => 'orang']) : route('search.index')) }}" class="absolute right-2.5 text-[var(--text-muted)] hover:text-[var(--primary)]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </form>
                    <button type="button" 
                            @click="mobileSearchOpen = false" 
                            class="px-2.5 py-1 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] shrink-0">
                        Batal
                    </button>
                </div>

                <!-- Left in Header: Mobile Hamburger Toggle + Brand for small screens ONLY (No duplicate desktop logo!) -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <button @click="mobileSidebarOpen = true" 
                            type="button"
                            class="lg:hidden p-2 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:bg-[var(--surface-hover)] hover:text-[var(--primary)] transition"
                            aria-label="Buka Menu Sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile Brand Icon: Visible ONLY on mobile (< lg), desktop logo lives cleanly in sidebar -->
                    <x-logo size="sm" :show-text="true" class="inline-flex lg:hidden" />

                    <!-- Desktop Context Title: Visible on lg+ -->
                    <div class="hidden lg:flex items-center gap-2 text-[17px] font-bold tracking-tight text-[var(--primary)] truncate">
                        @yield('header_title', 'Eksplorasi Karya')
                    </div>
                </div>

                <!-- Right in Header: Auth/User Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    
                    <!-- Search Trigger Button (opens overlay; rail has the desktop search at xl+) -->
                    <button type="button" 
                            @click="mobileSearchOpen = true; $nextTick(() => document.getElementById('nav-search-mobile')?.focus())"
                            class="xl:hidden w-9 h-9 rounded-lg flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition shrink-0"
                            aria-label="Cari">
                        <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- User Session Menu -->
                    @auth
                        <!-- Notification Bell + Dropdown -->
                        <div class="relative" x-data="notificationBell({ me: {{ auth()->id() }} })" x-init="init()">
                            <button @click="open = !open; if (open) refresh()"
                                    type="button"
                                    class="relative w-9 h-9 rounded-lg flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition"
                                    aria-label="Notifikasi"
                                    :aria-expanded="open">
                                <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unreadCount > 0" x-cloak class="hl-badge-dot" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute right-0 mt-2 w-80 bg-[var(--surface)] rounded-xl border border-[var(--border-color)] shadow-xl z-50 overflow-hidden">
                                <div class="flex items-center justify-between px-4 py-2.5 border-b border-[var(--border-color)]">
                                    <span class="hl-label">Notifikasi</span>
                                    <button @click="markAll()" x-show="unreadCount > 0" type="button" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Tandai dibaca
                                    </button>
                                </div>

                                <div class="max-h-96 overflow-y-auto divide-y divide-[#E2DFD7]/60 dark:divide-[#242426]">
                                    <template x-for="n in notifications" :key="n.id">
                                        <a :href="n.data.url || '#'"
                                           class="flex items-start gap-2.5 px-4 py-3 transition hover:bg-[var(--surface-hover)]"
                                           :class="!n.read ? 'bg-emerald-500/5 dark:bg-emerald-500/10' : ''">
                                            <img x-show="n.data.actor_avatar" :src="n.data.actor_avatar" alt="" class="w-8 h-8 rounded-lg object-cover ring-1 ring-[var(--border-color)] shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs text-[var(--primary)] leading-snug" x-text="n.data.message"></p>
                                                <p class="text-[11px] font-mono text-[var(--text-muted)] mt-0.5" x-text="n.created_at"></p>
                                            </div>
                                            <span x-show="!n.read" class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                        </a>
                                    </template>

                                    <template x-if="notifications.length === 0">
                                        <div class="px-4 py-8 text-center text-xs text-[var(--text-muted)]">
                                            Belum ada notifikasi baru.
                                        </div>
                                    </template>
                                </div>

                                <a href="{{ route('notifications.index') }}" class="block px-4 py-2.5 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 border-t border-[var(--border-color)] hover:bg-[var(--surface-hover)] transition">
                                    Lihat semua notifikasi
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Guest Actions -->
                        <div class="flex items-center gap-1 sm:gap-2 text-sm shrink-0">
                            <button type="button" @click="window.openAuthModal('login')" class="h-9 sm:h-10 px-3.5 sm:px-4 text-sm font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] inline-flex items-center justify-center rounded-lg transition cursor-pointer whitespace-nowrap">
                                Masuk
                            </button>
                            <button type="button" @click="window.openAuthModal('register')" class="btn-primary h-9 sm:h-10 px-4 text-sm font-bold rounded-lg cursor-pointer shrink-0 whitespace-nowrap">
                                Daftar
                            </button>
                        </div>
                    @endauth
                </div>

            </header>
            @endif

            <!-- MAIN WORKSPACE CONTENT CONTAINER -->
            <div class="w-full px-4 sm:px-6 py-6 flex-1 flex flex-col justify-between">
                <div>
                    @if(session('success'))
                        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2" role="alert">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="mb-5 p-3.5 rounded-xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs flex items-center gap-2" role="status">
                            <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <div class="space-y-6">
                        @yield('content')
                    </div>
                </div>
            </div>

        </main>

        <!-- RIGHT RAIL: global search + page rail (hidden below xl) -->
        @unless(View::hasSection('hide_right_rail'))
        <aside class="ny-rail hidden xl:block w-[340px] xl:w-[370px] shrink-0 sticky top-0 max-h-screen overflow-y-auto no-scrollbar pl-6 pr-1">
            <div class="sticky top-0 z-10 h-16 flex items-center bg-[var(--bg-app)]/85 backdrop-blur-md border-b border-[var(--border-color)] mb-5">
                <form action="{{ $searchAction }}" method="GET" class="relative w-full" role="search">
                    @if($onProfile)
                        <input type="hidden" name="u" value="{{ $profileUser }}">
                    @elseif(! $onCommunity && $onPeople)
                        <input type="hidden" name="tab" value="orang">
                    @endif
                    <svg class="w-4 h-4 text-[var(--text-muted)] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           id="nav-search"
                           name="q"
                           value="{{ $onProfile ? '' : request('q') }}"
                           placeholder="{{ $onProfile ? 'Cari karya @'.$profileUser.'...' : ($onCommunity ? 'Cari komunitas...' : 'Cari karya, akun, atau komunitas...') }}"
                           aria-label="{{ $onProfile ? 'Cari karya '.$profileUser : ($onCommunity ? 'Cari komunitas' : 'Cari karya, akun, atau komunitas') }}"
                           class="w-full pl-10 pr-9 py-2.5 text-sm rounded-xl bg-[var(--surface)] border border-[var(--border-color)] text-[var(--primary)] placeholder-[var(--text-muted)] outline-none focus:border-[var(--primary)] transition shadow-xs">
                </form>
            </div>

            <div class="pb-8">
                @hasSection('rail')
                    @yield('rail')
                @else
                    <div class="space-y-4">
                        @if(!empty($defaultRailTrending))
                            <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                                <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                                    <h3 class="text-[15px] font-bold text-[var(--primary)] flex items-center gap-2">
                                        <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        <span>Trending Hari Ini</span>
                                    </h3>
                                    <span class="text-[11px] font-mono text-[var(--text-muted)] uppercase tracking-wider">Top {{ count($defaultRailTrending) }}</span>
                                </div>
                                <div class="divide-y divide-[var(--border-color)]">
                                    @foreach($defaultRailTrending as $i => $tp)
                                        <a href="{{ route('projects.show', $tp['slug']) }}"
                                           class="flex items-center gap-3 px-5 py-3 hover:bg-[var(--surface-hover)] transition-colors group">
                                            <span class="text-xs font-mono {{ $i < 3 ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'font-medium text-[var(--text-muted)]' }} w-5 shrink-0 text-center">
                                                {{ sprintf('%02d', $i + 1) }}
                                            </span>
                                            <span class="w-10 h-10 rounded-lg overflow-hidden bg-[var(--surface-muted)] shrink-0 transition-colors flex items-center justify-center">
                                                @if(!empty($tp['thumbnail']))
                                                    <img src="{{ $tp['thumbnail'] }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-5 h-5 text-[var(--text-faint)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                    </svg>
                                                @endif
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-[13.5px] text-[var(--primary)] group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors truncate leading-snug">
                                                    {{ $tp['title'] }}
                                                </p>
                                                <p class="text-xs font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ $tp['username'] ?? 'anon' }}</p>
                                            </div>
                                            <span class="inline-flex items-center gap-1 shrink-0 text-xs font-mono font-semibold text-[var(--text-muted)] group-hover:text-[var(--primary)] transition-colors">
                                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                                </svg>
                                                <span>{{ number_format($tp['score'] ?? 0) }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                                <a href="{{ route('projects.index', ['tab' => 'trend']) }}"
                                   class="block px-5 py-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-[var(--surface-hover)] border-t border-[var(--border-color)] transition-colors">
                                    Lihat semua trending &rarr;
                                </a>
                            </div>
                        @endif

                        @if(isset($sidebarCommunities) && $sidebarCommunities->count() > 0)
                            <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden">
                                <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between">
                                    <h3 class="text-[15px] font-bold text-[var(--primary)] flex items-center gap-2">
                                        <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Komunitas Populer</span>
                                    </h3>
                                    <a href="{{ route('communities.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lihat semua</a>
                                </div>
                                <div class="divide-y divide-[var(--border-color)]">
                                    @foreach($sidebarCommunities as $comm)
                                        <a href="{{ route('communities.show', $comm->slug) }}"
                                           class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-[var(--surface-hover)] transition-colors group">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-[13.5px] text-[var(--primary)] group-hover:underline truncate">{{ $comm->name }}</p>
                                                <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">{{ $comm->members_count }} anggota</p>
                                            </div>
                                            <span class="text-xs font-medium text-[var(--text-muted)] group-hover:text-[var(--primary)] shrink-0">&rarr;</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Editorial footer (dipindah ke dasar rail kanan) -->
                @unless(View::hasSection('hide_footer'))
                <footer class="mt-6 pt-4 border-t border-[var(--border-color)] text-xs text-[var(--text-muted)] flex flex-col gap-3">
                    <div class="flex items-start gap-2">
                        <x-logo size="sm" />
                        <span>&bull;</span>
                        <span>Platform Showcase Project & Pengalaman Developer</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <a href="{{ route('projects.index') }}" class="hover:text-[var(--primary)] transition">Feed</a>
                        @auth
                            <a href="{{ route('projects.create') }}" class="hover:text-[var(--primary)] transition">Pamerkan</a>
                            <a href="{{ route('projects.bookmarks') }}" class="hover:text-[var(--primary)] transition">Koleksi</a>
                        @else
                            <button type="button" @click="window.openAuthModal('login')" class="hover:text-[var(--primary)] transition cursor-pointer">Masuk</button>
                        @endauth
                        <button type="button" @click="showHelpModal = true" class="hover:text-[var(--primary)] transition">Shortcuts (?)</button>
                        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--primary)] transition">GitHub</a>
                    </div>
                </footer>
                @endunless
            </div>
        </aside>
        @endunless

    </div>

    <!-- MOBILE BOTTOM NAVIGATION BAR -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[var(--bg-app)]/95 backdrop-blur-md border-t border-[var(--border-color)] px-3 py-2 flex items-center justify-around text-[11px] select-none"
         aria-label="Navigasi Utama Mobile">
        
        <!-- 1. Karya -->
        <a href="{{ route('projects.index') }}" 
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition {{ request()->routeIs('projects.index') && !request('tab') ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Karya</span>
        </a>

        <!-- 2. Komunitas -->
        <a href="{{ auth()->check() ? route('communities.index') : '#' }}"
           @guest @click.prevent="window.openAuthModal('login')" @endguest
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition {{ request()->routeIs('communities.*') ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Komunitas</span>
        </a>

        <!-- 3. Spaces -->
        <a href="{{ auth()->check() ? route('spaces.index') : '#' }}"
           @guest @click.prevent="window.openAuthModal('login')" @endguest
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition {{ request()->routeIs('spaces.*') ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
            </svg>
            <span>Spaces</span>
        </a>

        {{-- 4. Obrolan (auth) or Daftar (guest) --}}
        @auth
            <a href="{{ route('chat.index') }}" 
               class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition {{ request()->routeIs('chat.*') ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)]' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span>Obrolan</span>
            </a>
        @else
            <button type="button" @click="window.openAuthModal('register')"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Daftar</span>
            </button>
        @endauth

        {{-- 5. Koleksi (auth) or Masuk (guest) --}}
        @auth
            <a href="{{ route('projects.bookmarks') }}" 
               class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition {{ request()->routeIs('projects.bookmarks') ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)]' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <span>Koleksi</span>
            </a>
        @else
            <button type="button" @click="window.openAuthModal('login')"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-md transition text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Masuk</span>
            </button>
        @endauth

    </nav>

    <!-- KEYBOARD SHORTCUTS MODAL -->
    <div x-show="showHelpModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @click.self="showHelpModal = false"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-shortcuts-title">
        <div class="ny-card max-w-md w-full bg-[var(--surface)] p-6 space-y-4 rounded-2xl border border-[var(--border-color)] shadow-2xl"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-3">
                <h3 id="modal-shortcuts-title" class="font-bold text-sm text-[var(--primary)] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Shortcut Keyboard</span>
                </h3>
                <button @click="showHelpModal = false" 
                        type="button"
                        class="text-[var(--text-muted)] hover:text-[var(--primary)] inline-flex items-center gap-1 text-xs font-mono transition" 
                        aria-label="Tutup dialog shortcut">
                    <span>Esc</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Pilih project berikutnya</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">J</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Pilih project sebelumnya</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">K</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Buka detail project aktif</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">Enter</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Beri Upvote pada project aktif</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">U</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Simpan ke Koleksi (Bookmark)</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">B</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#E2DFD7]/60 dark:border-[#242426]">
                    <span class="text-[var(--text-muted)]">Fokus kolom pencarian</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">/ atau ⌘K</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-[var(--text-muted)]">Tutup dialog</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] text-[11px]">Esc</kbd>
                </div>
            </div>

            <div class="pt-2 text-center">
                <x-button @click="showHelpModal = false" variant="primary" size="sm">
                    Tutup
                </x-button>
            </div>
        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         x-cloak
         role="alert"
         aria-live="polite"
         class="fixed bottom-20 lg:bottom-6 right-6 z-50 flex items-center gap-2 bg-[var(--primary)] text-[var(--primary-text)] px-4 py-2.5 rounded-xl text-xs font-bold border border-[#242426] dark:border-[#E2DFD7] shadow-2xl">
        <svg class="w-4 h-4 text-emerald-400 dark:text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="toast.message"></span>
    </div>

    <!-- GLOBAL X-STYLE AUTH MODAL -->
    <x-auth-modal />

    <!-- GLOBAL REPORT MODAL -->
    <x-report-modal />

    <!-- GLOBAL ADMIN: WARN PROJECT MODAL -->
    <x-admin-warn-project-modal />

    <script>
        // Global auth modal opener helper
        window.openAuthModal = function(tab = 'login') {
            window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab } }));
        };

        // Auto trigger modal on load if query param ?auth=login or errors exist
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('auth') === 'login') {
                window.openAuthModal('login');
            } else if (params.get('auth') === 'register') {
                window.openAuthModal('register');
            }
            @if($errors->has('login') || $errors->has('email') || $errors->has('password') || session('auth_required'))
                window.openAuthModal('{{ $errors->has('name') || $errors->has('username') ? 'register' : 'login' }}');
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
