<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <title>@yield('title', 'NampungYuk — Platform Showcase Project Programmer')</title>
    <meta name="description" content="@yield('meta_description', 'Platform community untuk berbagi project web, software, coding, dan karya digital. Temukan inspirasi, tantangan, dan apa yang dipelajari developer.')">

    <!-- Fonts: Plus Jakarta Sans + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F6F8F7] dark:bg-[#0F1413] text-[#17211F] dark:text-[#F2F5F4] min-h-screen transition-colors duration-150 antialiased selection:bg-[#CCFBF1] selection:text-[#0F766E] relative overflow-x-hidden pb-16 lg:pb-0"
      x-data="{
          mobileSidebarOpen: false,
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
                  if (e.key === 'Escape') document.activeElement.blur();
                  return;
              }
              if (e.key === '/' || (e.metaKey && e.key === 'k') || (e.ctrlKey && e.key === 'k')) {
                  e.preventDefault();
                  document.getElementById('nav-search')?.focus();
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
             class="relative flex-1 flex flex-col max-w-xs w-full h-full bg-white dark:bg-[#151D1B] border-r border-[#DDE5E2] dark:border-[#24322F] shadow-xl z-10">
            
            <!-- Close Button -->
            <div class="absolute top-3 right-3 z-20">
                <button @click="mobileSidebarOpen = false" 
                        type="button"
                        class="p-1.5 rounded-lg text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
                        aria-label="Tutup Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @include('partials.sidebar')
        </div>
    </div>

    <!-- MAIN TWO-PANE LAYOUT -->
    <div class="min-h-screen flex">
        
        <!-- DESKTOP FIXED SIDEBAR -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 xl:w-72 shrink-0 sticky top-0 h-screen z-30 bg-white dark:bg-[#151D1B] border-r border-[#DDE5E2] dark:border-[#24322F]">
            @include('partials.sidebar')
        </aside>

        <!-- RIGHT WORKSPACE AREA -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- TOP HEADER -->
            <header class="sticky top-0 z-20 h-14 sm:h-16 bg-white/95 dark:bg-[#151D1B]/95 backdrop-blur-md border-b border-[#DDE5E2] dark:border-[#24322F] px-4 sm:px-6 flex items-center justify-between gap-3">
                
                <!-- Left in Header: Mobile Hamburger Toggle + Brand for small screens -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <button @click="mobileSidebarOpen = true" 
                            type="button"
                            class="lg:hidden p-1.5 rounded-lg border border-[#DDE5E2] dark:border-[#24322F] text-[#66736F] dark:text-[#8E9F9B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
                            aria-label="Buka Menu Sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile Brand Icon -->
                    <x-logo size="sm" class="lg:hidden" />
                </div>

                <!-- Center in Header: Universal Search -->
                <div class="flex-1 max-w-lg mx-1 sm:mx-4">
                    <form action="{{ route('projects.index') }}" method="GET" class="relative">
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('kategori')) <input type="hidden" name="kategori" value="{{ request('kategori') }}"> @endif
                        @if(request('tech')) <input type="hidden" name="tech" value="{{ request('tech') }}"> @endif
                        
                        <div class="relative flex items-center">
                            <svg class="w-4 h-4 text-[#66736F] dark:text-[#8E9F9B] absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" 
                                   id="nav-search"
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Cari project, teknologi, atau creator..." 
                                   aria-label="Cari project, teknologi, atau creator"
                                   class="w-full pl-9 sm:pl-10 pr-12 sm:pr-14 py-1.5 text-xs sm:text-sm bg-[#F6F8F7] dark:bg-[#0F1413] border border-[#DDE5E2] dark:border-[#24322F] rounded-lg text-[#17211F] dark:text-[#F2F5F4] placeholder-[#66736F]/60 transition">
                            
                            <div class="absolute right-2.5 hidden sm:flex items-center gap-1 pointer-events-none">
                                <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-bold text-[#66736F] dark:text-[#8E9F9B] bg-white dark:bg-[#151D1B] border border-[#DDE5E2] dark:border-[#24322F] rounded shadow-2xs">⌘K</kbd>
                            </div>

                            @if(request('q'))
                                <a href="{{ route('projects.index') }}" class="absolute right-8 text-[#66736F] hover:text-[#17211F] p-0.5" title="Hapus pencarian">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Right in Header: Quick Theme Toggle & Auth/User Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    
                    <!-- Theme Toggle Button -->
                    <button @click="$store.theme.toggle()"
                            type="button"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-[#66736F] hover:text-[#17211F] dark:text-[#8E9F9B] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
                            aria-label="Ganti mode gelap atau terang"
                            title="Mode Gelap / Terang">
                        <svg x-show="!$store.theme.dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="$store.theme.dark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    <!-- User Session Menu -->
                    @auth
                        <!-- Koleksi Link (Hanya Saat Login) -->
                        <a href="{{ route('projects.bookmarks') }}" 
                           title="Koleksi Project Tersimpan"
                           class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#66736F] dark:text-[#8E9F9B] hover:text-[#0F766E] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span class="hidden md:inline">Koleksi</span>
                        </a>

                        <!-- Keyboard Shortcuts Helper Button (Hanya Saat Login) -->
                        <button @click="showHelpModal = true"
                                type="button"
                                class="hidden sm:flex items-center justify-center w-8 h-8 rounded-lg text-[#66736F] hover:text-[#0F766E] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
                                aria-label="Bantuan shortcut keyboard"
                                title="Shortcut Keyboard (?)">
                            <span class="text-xs font-mono font-bold">?</span>
                        </button>

                        <!-- Upload CTA -->
                        <a href="{{ route('projects.create') }}" 
                           class="btn-primary py-1.5 px-3 text-xs shadow-xs hidden sm:inline-flex">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Pamer Kodingan</span>
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    type="button"
                                    class="flex items-center gap-1.5 p-0.5 rounded-lg hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
                                    aria-haspopup="true"
                                    :aria-expanded="open"
                                    aria-label="Menu akun {{ Auth::user()->name }}">
                                <x-user-avatar :user="Auth::user()" size="sm" />
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-cloak 
                                 class="absolute right-0 mt-2 w-52 bg-white dark:bg-[#151D1B] rounded-xl shadow-lg border border-[#DDE5E2] dark:border-[#24322F] py-1.5 z-50 text-xs">
                                <div class="px-4 py-2 border-b border-[#DDE5E2] dark:border-[#24322F]">
                                    <p class="font-bold truncate text-[#17211F] dark:text-[#F2F5F4]">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-[#66736F] dark:text-[#8E9F9B] font-mono truncate">&#64;{{ Auth::user()->username }}</p>
                                </div>
                                <a href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-2 px-4 py-2 text-[#17211F] dark:text-[#F2F5F4] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition">
                                    <svg class="w-3.5 h-3.5 text-[#66736F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('projects.bookmarks') }}" class="flex items-center gap-2 px-4 py-2 text-[#17211F] dark:text-[#F2F5F4] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition">
                                    <svg class="w-3.5 h-3.5 text-[#66736F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span>Koleksi Tersimpan</span>
                                </a>
                                <a href="{{ route('projects.create') }}" class="flex items-center gap-2 px-4 py-2 text-[#17211F] dark:text-[#F2F5F4] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition">
                                    <svg class="w-3.5 h-3.5 text-[#66736F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Pamerkan Project</span>
                                </a>
                                <div class="border-t border-[#DDE5E2] dark:border-[#24322F] my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Actions -->
                        <div class="flex items-center gap-2 text-xs">
                            <a href="{{ route('login') }}" class="px-3 py-1.5 text-[#66736F] dark:text-[#8E9F9B] hover:text-[#0F766E] font-medium transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="btn-primary py-1.5 px-3 text-xs shadow-xs">
                                Daftar
                            </a>
                        </div>
                    @endauth
                </div>

            </header>

            <!-- MAIN WORKSPACE CONTENT CONTAINER -->
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 flex flex-col justify-between">
                <div>
                    @if(session('success'))
                        <div class="mb-5 p-3.5 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/50 text-[#0F766E] dark:text-teal-300 text-xs flex items-center gap-2" role="alert">
                            <svg class="w-4 h-4 text-[#0F766E] dark:text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="mb-5 p-3.5 rounded-lg bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F] text-[#17211F] dark:text-[#F2F5F4] text-xs flex items-center gap-2" role="status">
                            <svg class="w-4 h-4 text-[#66736F] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <main class="space-y-6">
                        @yield('content')
                    </main>
                </div>

                <!-- Clean Editorial Footer -->
                <footer class="mt-16 pt-6 border-t border-[#DDE5E2] dark:border-[#24322F] text-xs text-[#66736F] dark:text-[#8E9F9B] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <x-logo size="sm" />
                        <span>&bull;</span>
                        <span>Platform Showcase Project & Pengalaman Developer</span>
                    </div>
                    <div class="flex items-center gap-4 text-[#66736F] dark:text-[#8E9F9B] text-xs">
                        <a href="{{ route('projects.index') }}" class="hover:text-[#0F766E] transition">Feed</a>
                        <a href="{{ route('projects.create') }}" class="hover:text-[#0F766E] transition">Pamerkan</a>
                        <a href="{{ route('projects.bookmarks') }}" class="hover:text-[#0F766E] transition">Koleksi</a>
                        <button @click="showHelpModal = true" class="hover:text-[#0F766E] transition">Shortcuts (?)</button>
                        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" rel="noopener noreferrer" class="hover:text-[#0F766E] transition">GitHub</a>
                    </div>
                </footer>
            </div>

        </div>

    </div>

    <!-- MOBILE BOTTOM NAVIGATION BAR (Sticky for Mobile-First Flow) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-[#151D1B]/95 backdrop-blur-md border-t border-[#DDE5E2] dark:border-[#24322F] px-2 py-1.5 flex items-center justify-around text-[10px] select-none"
         aria-label="Navigasi Utama Mobile">
        
        <!-- Home -->
        <a href="{{ route('projects.index') }}" 
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg transition {{ request()->routeIs('projects.index') && !request('tab') ? 'text-[#0F766E] dark:text-teal-400 font-bold' : 'text-[#66736F] dark:text-[#8E9F9B]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Home</span>
        </a>

        <!-- Explore / Trending -->
        <a href="{{ route('projects.index', ['tab' => 'trend']) }}" 
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg transition {{ request('tab') === 'trend' ? 'text-[#0F766E] dark:text-teal-400 font-bold' : 'text-[#66736F] dark:text-[#8E9F9B]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
            </svg>
            <span>Trending</span>
        </a>

        <!-- Upload CTA (Special Emphasis) -->
        <a href="{{ route('projects.create') }}" 
           class="flex flex-col items-center gap-0.5 px-3 py-1 -mt-3 text-white">
            <div class="w-10 h-10 rounded-full bg-[#0F766E] hover:bg-[#115E59] flex items-center justify-center shadow-md active:scale-95 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="text-[9px] font-semibold text-[#0F766E] dark:text-teal-400">Pamerkan</span>
        </a>

        <!-- Koleksi -->
        <a href="{{ route('projects.bookmarks') }}" 
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg transition {{ request()->routeIs('projects.bookmarks') ? 'text-[#0F766E] dark:text-teal-400 font-bold' : 'text-[#66736F] dark:text-[#8E9F9B]' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <span>Koleksi</span>
        </a>

        <!-- Profile or Login -->
        @auth
            <a href="{{ route('profile.show', Auth::user()->username) }}" 
               class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg transition {{ request()->routeIs('profile.show') ? 'text-[#0F766E] dark:text-teal-400 font-bold' : 'text-[#66736F] dark:text-[#8E9F9B]' }}">
                <x-user-avatar :user="Auth::user()" size="xs" />
                <span class="truncate max-w-[48px]">Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" 
               class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-lg transition text-[#66736F] dark:text-[#8E9F9B]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Masuk</span>
            </a>
        @endauth

    </nav>

    <!-- KEYBOARD SHORTCUTS MODAL -->
    <div x-show="showHelpModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @click.self="showHelpModal = false"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-shortcuts-title">
        <div class="ny-card max-w-md w-full shadow-xl bg-white dark:bg-[#151D1B] p-6 space-y-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between border-b border-[#DDE5E2] dark:border-[#24322F] pb-3">
                <h3 id="modal-shortcuts-title" class="font-bold text-sm text-[#17211F] dark:text-[#F2F5F4] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#0F766E]"></span>
                    <span>Shortcut Keyboard</span>
                </h3>
                <button @click="showHelpModal = false" 
                        type="button"
                        class="text-[#66736F] hover:text-[#17211F] dark:hover:text-white inline-flex items-center gap-1 text-xs font-mono transition" 
                        aria-label="Tutup dialog shortcut">
                    <span>Esc</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Pilih project berikutnya</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">J</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Pilih project sebelumnya</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">K</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Buka detail project aktif</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">Enter</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Beri Upvote pada project aktif</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">U</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Simpan ke Koleksi (Bookmark)</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">B</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-[#DDE5E2]/60 dark:border-[#24322F]/60">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Fokus kolom pencarian</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">/ atau ⌘K</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-[#66736F] dark:text-[#8E9F9B]">Tutup dialog</span>
                    <kbd class="px-2 py-0.5 font-mono font-bold rounded bg-[#EBF0EE] dark:bg-[#1F2C29] border border-[#DDE5E2] dark:border-[#24322F]">Esc</kbd>
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
         class="fixed bottom-20 lg:bottom-6 right-6 z-50 flex items-center gap-2 bg-[#17211F] dark:bg-[#1F2C29] text-white px-4 py-2.5 rounded-lg shadow-lg text-xs font-medium border border-[#24322F]">
        <svg class="w-4 h-4 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="toast.message"></span>
    </div>

    @stack('scripts')
</body>
</html>
