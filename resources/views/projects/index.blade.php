@extends('layouts.app')

@php
    $isPeopleView = ($view ?? null) === 'people';
@endphp

@section('title', 'NampungYuk — Platform Showcase Project & Pengalaman Developer')
@section('meta_description', 'Temukan karya project codingan terbaik dari developer Indonesia. Pelajari arsitektur, tech stack, dan tantangan yang mereka hadapi.')
@section('header_title', $isPeopleView ? 'Direktori Developer' : 'Eksplorasi Karya')

@section('content')
<div class="space-y-5" x-data="liveFeed()" x-init="init()">

    <!-- LIVE PULSE: notification when new projects arrive in real-time -->
    <div x-show="newCount > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="flex justify-center">
        <button @click="revealNew()"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] text-xs font-bold hover:opacity-90 transition shadow-xs active:scale-[0.98]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span x-text="newCount + ' project baru — klik untuk lihat'"></span>
        </button>
    </div>

    <!-- MAIN FEED / PEOPLE VIEW -->
    <div class="space-y-5 w-full">

        @if($view === 'people')
            <!-- PEOPLE (AKUN) RESULTS -->
            <div class="space-y-3.5">
                @forelse($people as $row)
                    @php $person = $row['user']; @endphp
                    <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-5 sm:p-6 flex items-start justify-between gap-4 transition-colors">
                        <a href="{{ route('profile.show', $person->username) }}" class="shrink-0">
                            <x-user-avatar :user="$person" size="lg" />
                        </a>
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $person->username) }}" class="font-bold text-base text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline transition truncate">
                                    {{ $person->name }}
                                </a>
                            </div>
                            <p class="text-xs font-mono text-[#6E6D67] dark:text-[#8E8D86]">&#64;{{ $person->username }}</p>
                            @if($person->bio)
                                <p class="text-sm text-[#6E6D67] dark:text-[#8E8D86] leading-relaxed line-clamp-2">{{ $person->bio }}</p>
                            @endif
                            <div class="flex items-center gap-3 pt-1 text-xs font-mono text-[#6E6D67] dark:text-[#8E8D86]">
                                <span><span class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1]">{{ number_format($row['followers']) }}</span> pengikut</span>
                                <span>&bull;</span>
                                <span><span class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1]">{{ number_format($row['projects']) }}</span> karya</span>
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

            <!-- ACTIVE FILTER CHIPS BAR -->
            @if($categorySlug || $techFilter || $search || ($searchOwner ?? null))
                <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[#6E6D67] dark:text-[#8E8D86] font-medium">Filter aktif:</span>
                        @if($searchOwner ?? null)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] text-[11px] border border-[#E2DFD7] dark:border-[#242426]">
                                Karya &#64;{{ $searchOwner->username }}
                            </span>
                        @endif
                        @if($categorySlug)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] text-[11px] border border-[#E2DFD7] dark:border-[#242426]">
                                Kategori: {{ $categorySlug }}
                            </span>
                        @endif
                        @if($techFilter)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] font-mono text-[11px] border border-[#E2DFD7] dark:border-[#242426]">
                                #{{ $techFilter }}
                            </span>
                        @endif
                        @if($search)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] text-[11px] border border-[#E2DFD7] dark:border-[#242426]">
                                "{{ $search }}"
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('projects.index', array_filter(['tab' => $tab])) }}"
                       class="inline-flex items-center gap-1 text-[#6E6D67] hover:text-rose-600 dark:text-[#8E8D86] dark:hover:text-rose-400 text-xs font-semibold shrink-0 transition-colors">
                        <span>Reset</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            @endif

            <!-- MAIN PROJECT FEED -->
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
                    <div x-ref="sentinel" class="pt-6 flex justify-center">
                        <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                                class="btn-secondary text-xs py-2 px-4 disabled:opacity-60 cursor-pointer">
                            <span x-show="!loading">Muat lebih banyak</span>
                            <span x-show="loading" x-cloak>Memuat...</span>
                        </button>
                        <p x-show="done && {{ $projects->total() > 0 ? 'true' : 'false' }}" x-cloak class="text-[11px] text-zinc-500 dark:text-zinc-400 py-2">
                            Kamu sudah di akhir — {{ $projects->total() }} project.
                        </p>
                    </div>
                @else
                    <!-- GUEST DISCOVERY BANNER -->
                    <div class="mt-8 pt-6 pb-2 border-t border-[#E2DFD7] dark:border-[#242426]">
                        <div class="p-6 sm:p-8 rounded-2xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] text-center space-y-4 shadow-xs relative overflow-hidden">
                            <div class="w-11 h-11 mx-auto rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] flex items-center justify-center font-bold text-lg shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>

                            <div class="space-y-1 max-w-md mx-auto">
                                <h3 class="text-base sm:text-lg font-bold text-[#0B0B0D] dark:text-[#EAE8E1]">Ingin memamerkan karya kodinganmu?</h3>
                                <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] leading-relaxed">
                                    Daftar gratis untuk mengunggah project, live demo interaktif, menyimpan koleksi bookmark, dan berdiskusi dengan developer se-Indonesia.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5 pt-2">
                                <button type="button" @click="window.openAuthModal('register')" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-zinc-950 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 font-semibold text-xs transition shadow-xs cursor-pointer">
                                    Buat Akun Gratis
                                </button>
                                <button type="button" @click="window.openAuthModal('login')" class="w-full sm:w-auto px-5 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition cursor-pointer">
                                    Masuk ke Akun
                                </button>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        @endif

    </div>

</div>
@endsection

@section('rail')
    <div class="space-y-4">

        <!-- BLOCK 0: Who to follow (Social onboarding) -->
        @auth
            @if(isset($suggestedDevelopers) && $suggestedDevelopers->count() > 0)
                <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-bold text-[#0B0B0D] dark:text-[#EAE8E1] flex items-center gap-2">
                            <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>Untuk Diikuti</span>
                        </h3>
                        <a href="{{ route('projects.index', ['view' => 'people']) }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lihat semua</a>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($suggestedDevelopers as $dev)
                            <div class="flex items-center justify-between gap-2.5">
                                <a href="{{ route('profile.show', $dev->username) }}" class="flex items-center gap-3 min-w-0 group">
                                    <x-user-avatar :user="$dev" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[13.5px] text-[#0B0B0D] dark:text-[#EAE8E1] group-hover:underline transition truncate">
                                            {{ $dev->name }}
                                        </p>
                                        <p class="text-xs font-mono text-[#6E6D67] dark:text-[#8E8D86] truncate">&#64;{{ $dev->username }}</p>
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
        <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-5 space-y-3.5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-[#0B0B0D] dark:text-[#EAE8E1] flex items-center gap-2">
                    <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>Trending Hari Ini</span>
                </h3>
                <a href="{{ route('projects.index', ['tab' => 'trend']) }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lihat semua</a>
            </div>

            <div class="space-y-1">
                @forelse($trendingToday as $i => $tp)
                    <a href="{{ route('projects.show', $tp->slug) }}"
                       class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#EFECE6] dark:hover:bg-[#19191D] transition-colors group">
                        <span class="text-xs font-mono {{ $i < 3 ? 'font-bold text-[#0B0B0D] dark:text-[#EAE8E1]' : 'font-medium text-[#6E6D67] dark:text-[#8E8D86]' }} w-5 shrink-0">{{ sprintf('%02d', $i + 1) }}</span>
                        <span class="w-11 h-11 rounded-lg overflow-hidden bg-[#EFECE6] dark:bg-[#161619] shrink-0 border border-[#E2DFD7] dark:border-[#242426] flex items-center justify-center">
                            @if($tp->thumbnail)
                                <img src="{{ $tp->thumbnail }}" alt="{{ $tp->title }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-[#8E8D86] dark:text-[#605F5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-[13.5px] text-[#0B0B0D] dark:text-[#EAE8E1] group-hover:underline transition truncate leading-snug">
                                {{ $tp->title }}
                            </p>
                            <p class="text-xs font-mono text-[#6E6D67] dark:text-[#8E8D86] truncate mt-0.5">&#64;{{ $tp->user?->username ?? 'anon' }}</p>
                        </div>
                        <span class="text-xs font-mono font-semibold text-[#0B0B0D] dark:text-[#EAE8E1] px-2 py-1 rounded-md bg-[#EFECE6] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] shrink-0 inline-flex items-center gap-1 group-hover:border-[#D0CDC3] dark:group-hover:border-[#333336] transition-colors">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                            </svg>
                            <span>{{ number_format($tp->score) }}</span>
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] py-3 text-center">Belum ada project trending hari ini.</p>
                @endforelse
            </div>
        </div>

        <!-- BLOCK: Sedang Hangat Dibicarakan (most commented) -->
        <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-5 space-y-3.5">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-[#0B0B0D] dark:text-[#EAE8E1] flex items-center gap-2">
                    <svg class="w-[18px] h-[18px] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    <span>Sedang Hangat Dibicarakan</span>
                </h3>
                <span class="text-[11px] font-mono text-[#6E6D67] dark:text-[#8E8D86]">Topik</span>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-0.5">
                @forelse($hotHashtags as $tag => $count)
                    <a href="{{ route('projects.index', ['tech' => $tag]) }}"
                       title="#{{ $tag }} ({{ $count }} karya)"
                       class="flex items-center justify-between gap-1.5 px-3 py-2 rounded-xl bg-[#EFECE6]/70 dark:bg-[#161619] hover:bg-[#EFECE6] dark:hover:bg-[#19191D] border border-[#E2DFD7] dark:border-[#242426] transition-colors group min-w-0">
                        <span class="inline-flex items-center gap-1 min-w-0">
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono text-xs font-bold shrink-0">#</span>
                            <span class="text-xs font-mono font-medium text-[#0B0B0D] dark:text-[#EAE8E1] group-hover:underline truncate">{{ $tag }}</span>
                        </span>
                        <span class="text-[11px] font-mono text-[#6E6D67] dark:text-[#8E8D86] font-semibold shrink-0">{{ $count }}</span>
                    </a>
                @empty
                    <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] py-3 text-center col-span-2">Belum ada hashtag hangat.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection
