<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <title>@yield('title', 'NampungYuk — Showcase Project Programmer')</title>
    <meta name="description" content="@yield('meta_description', 'Platform showcase karya codingan programmer. Pamerkan project web, mobile, AI, CLI, dan open-source karyamu!')">

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
<body class="bg-neutral-100 dark:bg-[#050505] bg-dot-grid text-neutral-900 dark:text-neutral-100 min-h-screen transition-colors duration-200 antialiased selection:bg-orange-500 selection:text-white relative overflow-x-hidden"
      x-data="{
          mobileSidebarOpen: false,
          activeCardIndex: -1,
          showHelpModal: false,
          toast: { show: false, message: '' },
          notify(msg) {
              this.toast.message = msg;
              this.toast.show = true;
              setTimeout(() => { this.toast.show = false; }, 2800);
          },
          handleKeydown(e) {
              if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
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
              } else if ((e.key === 'u' || e.key === 'l') && this.activeCardIndex >= 0) {
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

    <!-- AMBIENT TOP LIGHTING BEAM (VERCEL STYLE) -->
    <div class="ambient-beam"></div>

    <!-- MOBILE SIDEBAR BACKDROP & DRAWER -->
    <div x-show="mobileSidebarOpen" 
         x-cloak 
         class="fixed inset-0 z-50 lg:hidden"
         role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs"></div>

        <!-- Off-canvas Mobile Menu -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-in-out duration-250 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-250 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex-1 flex flex-col max-w-xs w-full h-full bg-white dark:bg-[#0c0c0e] border-r border-neutral-200 dark:border-white/[0.08] shadow-2xl z-10">
            
            <!-- Close Button -->
            <div class="absolute top-3 right-3 z-20">
                <button @click="mobileSidebarOpen = false" 
                        class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/5 transition"
                        title="Tutup Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @include('partials.sidebar')
        </div>
    </div>

    <!-- MAIN APPLICATION WRAPPER (TWO-PANE WORKSPACE) -->
    <div class="min-h-screen flex">
        
        <!-- DESKTOP FIXED/STICKY LEFT SIDEBAR -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 xl:w-72 shrink-0 sticky top-0 h-screen z-30 bg-white/95 dark:bg-[#09090b]/95 backdrop-blur-md border-r border-neutral-200/80 dark:border-white/[0.08]">
            @include('partials.sidebar')
        </aside>

        <!-- RIGHT WORKSPACE AREA -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- TOP WORKSTATION HEADER -->
            <header class="sticky top-0 z-20 h-14 sm:h-16 bg-white/80 dark:bg-[#09090b]/80 backdrop-blur-md border-b border-neutral-200/80 dark:border-white/[0.08] px-3 sm:px-6 flex items-center justify-between gap-3">
                
                <!-- Left in Header: Mobile Hamburger Toggle + Brand for small screens -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Mobile Hamburger -->
                    <button @click="mobileSidebarOpen = true" 
                            type="button"
                            class="lg:hidden p-1.5 rounded-xl border border-neutral-200 dark:border-white/10 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition"
                            aria-label="Buka Menu Sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile Brand Icon (only visible on mobile/tablet) -->
                    <a href="{{ route('projects.index') }}" class="flex lg:hidden items-center gap-2 group">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-500 via-amber-500 to-orange-600 flex items-center justify-center text-white font-mono font-bold text-xs shadow-xs">
                            <span>{;}</span>
                        </div>
                        <span class="text-sm font-extrabold tracking-tight text-neutral-900 dark:text-white leading-none">
                            Nampung<span class="text-orange-500">Yuk</span>
                        </span>
                    </a>
                </div>

                <!-- Center in Header: Universal Search with Command Palette Badge -->
                <div class="flex-1 max-w-lg mx-1 sm:mx-4">
                    <form action="{{ route('projects.index') }}" method="GET" class="relative">
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                        @if(request('kategori')) <input type="hidden" name="kategori" value="{{ request('kategori') }}"> @endif
                        @if(request('tech')) <input type="hidden" name="tech" value="{{ request('tech') }}"> @endif
                        
                        <div class="relative flex items-center">
                            <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" 
                                   id="nav-search"
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Cari karya kodingan, tech stack (Vue, Go, AI)..." 
                                   class="w-full pl-9 sm:pl-10 pr-12 sm:pr-14 py-1.5 text-xs sm:text-sm bg-neutral-100/90 dark:bg-[#111113] border border-neutral-200 dark:border-white/[0.08] rounded-xl text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:border-orange-500 dark:focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
                            
                            <div class="absolute right-2.5 hidden sm:flex items-center gap-1 pointer-events-none">
                                <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-bold text-neutral-400 dark:text-neutral-400 bg-white dark:bg-[#1a1a1e] border border-neutral-200 dark:border-white/10 rounded shadow-2xs">⌘K</kbd>
                            </div>

                            @if(request('q'))
                                <a href="{{ route('projects.index') }}" class="absolute right-8 text-neutral-400 hover:text-neutral-200 p-0.5" title="Hapus pencarian">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Right in Header: Quick Theme Toggle & Auth/User Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    
                    <!-- Theme Toggle Button -->
                    <button @click="$store.theme.toggle()"
                            type="button"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/[0.05] transition"
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
                        <!-- Koleksi / Bookmarks Link (Hanya Saat Login) -->
                        <a href="{{ route('projects.bookmarks') }}" 
                           title="Koleksi Project Tersimpan"
                           class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-neutral-600 dark:text-neutral-300 hover:text-orange-500 dark:hover:text-orange-400 hover:bg-neutral-100 dark:hover:bg-white/[0.04] transition">
                            <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span class="hidden md:inline">Koleksi</span>
                        </a>

                        <!-- Keyboard Shortcuts Helper Button (Hanya Saat Login) -->
                        <button @click="showHelpModal = true"
                                type="button"
                                class="hidden sm:flex items-center justify-center w-7 h-7 rounded-lg text-neutral-400 hover:text-orange-500 hover:bg-neutral-100 dark:hover:bg-white/[0.05] transition"
                                title="Shortcut Keyboard (?)">
                            <span class="text-xs font-mono font-bold">?</span>
                        </button>

                        <!-- Pamer Kodingan Button (Hanya Saat Login) -->
                        <a href="{{ route('projects.create') }}" 
                           class="btn-vercel-primary inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 text-xs rounded-lg shadow-sm active:scale-95 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">Pamer Kodingan</span>
                            <span class="sm:hidden">Pamer</span>
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-1.5 p-1 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <img src="{{ Auth::user()->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode(Auth::user()->name) }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="w-7 h-7 rounded-lg bg-neutral-200 dark:bg-neutral-800 object-cover ring-1 ring-neutral-300 dark:ring-neutral-700">
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-cloak 
                                 class="absolute right-0 mt-2 w-52 bg-white dark:bg-[#121212] rounded-2xl shadow-2xl border border-neutral-200 dark:border-neutral-800 py-1.5 z-50 text-xs">
                                <div class="px-4 py-2.5 border-b border-neutral-100 dark:border-neutral-800">
                                    <p class="font-bold truncate text-neutral-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono truncate">&#64;{{ Auth::user()->username }}</p>
                                </div>
                                <a href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-2 px-4 py-2 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition">
                                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('projects.bookmarks') }}" class="flex items-center gap-2 px-4 py-2 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition">
                                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span>Koleksi Saya</span>
                                </a>
                                <a href="{{ route('projects.create') }}" class="flex items-center gap-2 px-4 py-2 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition">
                                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Pamerkan Kodingan</span>
                                </a>
                                <div class="border-t border-neutral-100 dark:border-neutral-800 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest: Belum Login -->
                        <div class="flex items-center gap-1.5 text-xs font-semibold">
                            <a href="{{ route('login') }}" class="px-3 py-1.5 text-neutral-600 dark:text-neutral-300 hover:text-orange-500 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="btn-vercel-primary inline-flex items-center px-3.5 py-1.5 text-xs rounded-lg shadow-2xs transition">
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
                        <div class="mb-5 p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="mb-5 p-3.5 rounded-2xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-neutral-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <main class="space-y-6">
                        @yield('content')
                    </main>
                </div>

                <!-- Clean Minimalist Footer -->
                <footer class="mt-16 pt-8 border-t border-neutral-200 dark:border-neutral-800/80 text-xs text-neutral-500 dark:text-neutral-400 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-neutral-900 dark:text-neutral-200 font-mono">{;} NampungYuk</span>
                        <span>&bull;</span>
                        <span>Pameran Kodingan Developer</span>
                    </div>
                    <div class="flex items-center gap-4 text-neutral-400 font-mono text-[11px]">
                        @auth
                            <a href="{{ route('projects.create') }}" class="hover:text-orange-500 transition">Pamerkan</a>
                            <a href="{{ route('projects.bookmarks') }}" class="hover:text-orange-500 transition">Koleksi</a>
                            <button @click="showHelpModal = true" class="hover:text-orange-500 transition">Shortcuts (?)</button>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-orange-500 transition">Masuk</a>
                            <a href="{{ route('register') }}" class="hover:text-orange-500 transition">Daftar</a>
                        @endauth
                        <a href="https://github.com/GlorysID/NampungYuk" target="_blank" rel="noopener noreferrer" class="hover:text-orange-500 transition">GitHub</a>
                    </div>
                </footer>
            </div>

        </div>

    </div>

    <!-- KEYBOARD SHORTCUTS MODAL -->
    <div x-show="showHelpModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @click.self="showHelpModal = false">
        <div class="double-bezel max-w-md w-full shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="bezel-core p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3">
                    <h3 class="font-bold text-sm text-neutral-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        <span>Keyboard Shortcuts (Raycast / Linear Style)</span>
                    </h3>
                    <button @click="showHelpModal = false" class="text-neutral-400 hover:text-white inline-flex items-center gap-1 text-xs font-mono transition" title="Tutup (Esc)">
                        <span>Esc</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Pilih proyek berikutnya</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">J</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Pilih proyek sebelumnya</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">K</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Buka detail proyek aktif</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">Enter</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Beri Upvote pada proyek aktif</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">U</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Simpan ke Koleksi (Bookmark)</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">B</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800/60">
                        <span class="text-neutral-600 dark:text-neutral-300">Fokus kolom pencarian</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">/ atau ⌘K</kbd>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-neutral-600 dark:text-neutral-300">Tutup modal / drawer</span>
                        <kbd class="px-2 py-1 font-mono font-bold rounded bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">Esc</kbd>
                    </div>
                </div>

                <div class="pt-2 text-center">
                    <button @click="showHelpModal = false" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 hover:bg-neutral-800 dark:hover:bg-neutral-700 text-white rounded-xl text-xs font-semibold">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-neutral-950 dark:bg-[#161616] text-white px-4 py-3 rounded-2xl shadow-2xl text-xs font-medium border border-neutral-800">
        <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="toast.message"></span>
    </div>

    @stack('scripts')
</body>
</html>
