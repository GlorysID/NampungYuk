@extends('layouts.app')

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')

@section('content')
<div class="space-y-6" x-data="liveFeed()" x-init="init()">

    <!-- LIVE PULSE: muncul saat ada project baru (real-time via Reverb) -->
    <div x-show="newCount > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="flex justify-center">
        <button @click="revealNew()"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#0070f3] text-[#ffffff] text-xs font-bold hover:bg-[#0761d1] transition active:scale-[0.98]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#ffffff] animate-pulse"></span>
            <span x-text="newCount + ' project baru — klik untuk lihat'"></span>
        </button>
    </div>

    <!-- 2-COLUMN WORKSPACE: MAIN FEED (XL: 7 COLS) + COMMUNITY SIDEBAR (XL: 5 COLS) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start w-full">

        <!-- MAIN SHOWCASE FEED (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4 w-full">

            <!-- RESULT VIEW SWITCH: Karya (projects) | Akun (people) — only meaningful when searching -->
            @if($search || $view === 'people')
                <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <a href="{{ route('projects.index', array_filter(['q' => $search, 'tab' => request('tab')])) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $view !== 'people' ? 'bg-[#0070f3] text-white' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        <span>Karya</span>
                    </a>
                    <a href="{{ route('projects.index', array_filter(['q' => $search, 'view' => 'people'])) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $view === 'people' ? 'bg-[#0070f3] text-white' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Akun</span>
                    </a>
                </div>
            @endif

        @if($view === 'people')
            <!-- PEOPLE (AKUN) RESULTS -->
            <div class="space-y-3">
                @forelse($people as $row)
                    @php $person = $row['user']; @endphp
                    <div class="ny-card p-4 sm:p-5 bg-white dark:bg-[#0a0a0a] flex items-start justify-between gap-4">
                        <a href="{{ route('profile.show', $person->username) }}" class="shrink-0">
                            <x-user-avatar :user="$person" size="lg" />
                        </a>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $person->username) }}" class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition">
                                    {{ $person->name }}
                                </a>
                                @if($person->is_admin ?? false)
                                    <x-admin-badge size="xs" />
                                @endif
                            </div>
                            <p class="text-xs font-mono text-[#63636b] dark:text-[#a0a0a0]">&#64;{{ $person->username }}</p>
                            @if($person->bio)
                                <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-2">{{ $person->bio }}</p>
                            @endif
                            <div class="flex items-center gap-3 pt-0.5 text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0]">
                                <span><span class="font-bold text-[#18181b] dark:text-[#fafafa]">{{ number_format($row['followers']) }}</span> pengikut</span>
                                <span>&bull;</span>
                                <span><span class="font-bold text-[#18181b] dark:text-[#fafafa]">{{ number_format($row['projects']) }}</span> karya</span>
                            </div>
                        </div>
                        <div class="shrink-0 pt-0.5">
                            <x-follow-button :user="$person" size="sm" />
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        title="Akun tidak ditemukan"
                        description="{{ $search ? 'Tidak ada akun dengan nama atau username \"'.$search.'\".' : 'Belum ada akun untuk ditampilkan.' }}"
                        action-label="Kembali ke Semua Karya"
                        :action-url="route('projects.index')" />
                @endforelse

                @if($people->hasPages())
                    <div class="pt-2">{{ $people->links() }}</div>
                @endif
            </div>
        @else

            <!-- ACTIVE FILTER BAR (Only visible when filter or search is active) -->
            @if($categorySlug || $techFilter || $search || ($searchOwner ?? null))
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[#63636b] dark:text-[#a0a0a0] font-medium">Filter aktif:</span>
                        @if($searchOwner ?? null)
                            <span class="px-2 py-0.5 rounded-md bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-medium text-[11px] border border-[#3291ff]/30 dark:border-[#3291ff]/30">
                                Karya &#64;{{ $searchOwner->username }}
                            </span>
                        @endif
                        @if($categorySlug)
                            <span class="px-2 py-0.5 rounded-md bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] font-medium text-[11px] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="px-2 py-0.5 rounded-md bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] font-mono text-[11px] border border-[#3291ff]/30 dark:border-[#3291ff]/30">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="px-2 py-0.5 rounded-md bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] text-[11px] border border-[#e4e4e7] dark:border-[#1f1f1f]">
                                "{{ $search }}"
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('projects.index', array_filter(['tab' => $tab])) }}" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 text-xs font-semibold shrink-0">
                        <span>Reset</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            @endif

            <!-- MAIN PROJECT FEED (infinite scroll for auth, gated preview for guest) -->
            <div x-data="infiniteFeed({
                    nextPageUrl: {{ (Auth::check() && $projects->hasMorePages()) ? Illuminate\Support\Js::from(route('feeds.projects', request()->query() + ['page' => $projects->currentPage() + 1])) : 'null' }},
                    lastPage: {{ $projects->lastPage() }}
                 })">
                <div class="space-y-4" x-ref="items" data-feed-items>
                    @forelse($projects as $project)
                        @php
                            $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                            $initialVote = $userVotes[$project->id] ?? null;
                        @endphp
                        <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="in_array($project->id, $userRepostedIds ?? [])" />
                    @empty
                        <x-empty-state 
                            title="Tidak ada project ditemukan" 
                            description="{{ ($searchOwner ?? null) ? 'Belum ada karya @'.$searchOwner->username.' yang cocok dengan pencarian.' : 'Belum ada karya kodingan yang cocok dengan kriteria pencarian atau filter yang dipilih.' }}"
                            action-label="{{ ($searchOwner ?? null) ? 'Lihat Profil @'.$searchOwner->username : 'Kembali ke Semua Project' }}"
                            :action-url="($searchOwner ?? null) ? route('profile.show', $searchOwner->username) : route('projects.index')" />
                    @endforelse
                </div>

                @auth
                    <!-- Infinite scroll sentinel + fallback button for authenticated users -->
                    <div x-ref="sentinel" class="pt-4 flex justify-center">
                        <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                                class="btn-secondary text-xs py-2.5 px-5 disabled:opacity-60 cursor-pointer">
                            <span x-show="!loading">Muat lebih banyak</span>
                            <span x-show="loading" x-cloak>Memuat...</span>
                        </button>
                        <p x-show="done && {{ $projects->total() > 0 ? 'true' : 'false' }}" x-cloak class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] py-2">
                            Kamu sudah di akhir — {{ $projects->total() }} project.
                        </p>
                    </div>
                @else
                    <!-- X-STYLE GATED SCROLL WALL FOR GUESTS -->
                    <div class="mt-6 pt-6 pb-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f] relative">
                        <div class="ny-card p-6 sm:p-8 bg-gradient-to-b from-[#ffffff] to-[#f8f9fa] dark:from-[#0a0a0a] dark:to-[#000000] border border-[#e4e4e7] dark:border-[#27272a] text-center space-y-4 shadow-xl relative overflow-hidden">
                            <!-- Background glow -->
                            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-48 h-48 rounded-full bg-[#0070f3]/15 blur-3xl pointer-events-none"></div>

                            <div class="w-12 h-12 mx-auto rounded-2xl ny-gradient-bg text-white flex items-center justify-center font-bold text-xl shadow-lg relative z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>

                            <div class="space-y-1 relative z-10 max-w-md mx-auto">
                                <h3 class="text-base sm:text-lg font-bold text-[#18181b] dark:text-white">Ingin menjelajahi lebih banyak karya?</h3>
                                <p class="text-xs text-[#63636b] dark:text-[#a1a1aa] leading-relaxed">
                                    Akses penuh ke seluruh karya developer, fitur bookmark, apresiasi upvote, dan ruang diskusi tersedia gratis dengan membuat akun.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5 pt-2 relative z-10">
                                <button type="button" @click="window.openAuthModal('register')" class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-white text-black font-bold text-xs hover:bg-[#e4e4e7] transition shadow-md cursor-pointer">
                                    Buat Akun Gratis
                                </button>
                                <button type="button" @click="window.openAuthModal('login')" class="w-full sm:w-auto px-6 py-2.5 rounded-full border border-[#e4e4e7] dark:border-[#3f3f46] text-xs font-semibold text-[#18181b] dark:text-white hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                                    Masuk ke Akun
                                </button>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        @endif

        </div>

        <!-- SECONDARY SIDEBAR: COMMUNITY BLOCKS (xl:col-span-4) - FIXED/STICKY -->
        <aside class="xl:col-span-4 sticky top-20 self-start space-y-4 max-h-[calc(100vh-5.5rem)] overflow-y-auto pr-1">

            <!-- BLOCK 0: Who to follow (Social onboarding) -->
            @auth
                @if(isset($suggestedDevelopers) && $suggestedDevelopers->count() > 0)
                    <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#0a0a0a]">
                        <div class="flex items-center justify-between">
                            <h3 class="hl-label flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span>Untuk Diikuti</span>
                            </h3>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($suggestedDevelopers as $dev)
                                <div class="flex items-center justify-between gap-2">
                                    <a href="{{ route('profile.show', $dev->username) }}" class="flex items-center gap-2.5 min-w-0 group">
                                        <x-user-avatar :user="$dev" size="sm" />
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">
                                                {{ $dev->name }}
                                            </p>
                                            <p class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $dev->username }}</p>
                                        </div>
                                    </a>
                                    <x-follow-button :user="$dev" size="sm" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            <!-- BLOCK 2: Trending Projects Today -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#0a0a0a]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span>Trending Hari Ini</span>
                    </h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($trendingToday as $i => $tp)
                        @guest
                            <button type="button"
                                    @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                    class="w-full text-left flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group cursor-pointer">
                                <span class="text-xs font-mono font-bold text-[#63636b] dark:text-[#a0a0a0] w-4 shrink-0">{{ $i + 1 }}</span>
                                <span class="w-8 h-8 rounded-lg overflow-hidden bg-[#eeeeef] dark:bg-[#171717] shrink-0">
                                    @if($tp->thumbnail)
                                        <img src="{{ $tp->thumbnail }}" alt="" class="w-full h-full object-cover">
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">
                                        {{ $tp->title }}
                                    </p>
                                    <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $tp->user?->username ?? 'anon' }}</p>
                                </div>
                                <span class="text-[11px] font-mono font-semibold text-[#0070f3] dark:text-[#3291ff] px-2 py-0.5 rounded bg-[#e6f0ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 shrink-0 inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                    {{ $tp->score }}
                                </span>
                            </button>
                        @else
                            <a href="{{ route('projects.show', $tp->slug) }}"
                               class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
                                <span class="text-xs font-mono font-bold text-[#63636b] dark:text-[#a0a0a0] w-4 shrink-0">{{ $i + 1 }}</span>
                                <span class="w-8 h-8 rounded-lg overflow-hidden bg-[#eeeeef] dark:bg-[#171717] shrink-0">
                                    @if($tp->thumbnail)
                                        <img src="{{ $tp->thumbnail }}" alt="" class="w-full h-full object-cover">
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">
                                        {{ $tp->title }}
                                    </p>
                                    <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $tp->user?->username ?? 'anon' }}</p>
                                </div>
                                <span class="text-[11px] font-mono font-semibold text-[#0070f3] dark:text-[#3291ff] px-2 py-0.5 rounded bg-[#e6f0ff] dark:bg-[#3291ff]/12 border border-[#3291ff]/30 shrink-0 inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                    {{ $tp->score }}
                                </span>
                            </a>
                        @endguest
                    @empty
                        <p class="text-xs text-[#63636b] py-2 text-center">Belum ada project trending.</p>
                    @endforelse
                </div>
            </div>

            <!-- BLOCK: Sedang Hangat Dibicarakan (most commented) -->
            <div class="ny-card p-4 space-y-3 bg-white dark:bg-[#0a0a0a]">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                        </svg>
                        <span>Sedang Hangat Dibicarakan</span>
                    </h3>
                </div>

                <div class="flex flex-wrap gap-1.5 pt-0.5">
                    @forelse($hotHashtags as $tag => $count)
                        <a href="{{ route('projects.index', ['tech' => $tag]) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] border border-[#e4e4e7] dark:border-[#1f1f1f] hover:border-[#0070f3] dark:hover:border-[#3291ff] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition">
                            <span class="text-[#0070f3] dark:text-[#3291ff]">#</span>
                            <span class="truncate max-w-[120px]">{{ $tag }}</span>
                            <span class="text-[10px] font-mono text-[#63636b] dark:text-[#a0a0a0]">{{ $count }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-[#63636b] py-2 text-center w-full">Belum ada hashtag hangat.</p>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

</div>
@endsection
