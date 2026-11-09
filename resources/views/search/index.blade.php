@extends('layouts.app')

@section('title', ($term !== '' ? $term . ' — Cari di NampungYuk' : 'Pencarian — NampungYuk'))
@section('header_title', 'Pencarian')

@section('content')
<div class="space-y-5">

    <!-- STICKY TOP SEARCH BAR + TABS (X/Twitter style, pinned directly below top header) -->
    <div class="sticky top-16 z-10 -mx-4 sm:-mx-6 -mt-6 bg-[var(--bg-app)]/95 backdrop-blur-md border-b border-[var(--border-color)]">
        
        <!-- Search Bar Row -->
        <div class="px-4 sm:px-6 py-3 flex items-center gap-3">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('projects.index') }}"
               class="w-9 h-9 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition-colors shrink-0"
               aria-label="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>

            <form action="{{ route('search.index') }}" method="GET" class="flex-1 relative flex items-center" role="search">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <svg class="w-4 h-4 text-[var(--text-muted)] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       name="q"
                       value="{{ $term }}"
                       @if($term === '') autofocus @endif
                       placeholder="Cari karya, akun, atau komunitas..."
                       aria-label="Cari karya, akun, atau komunitas"
                       class="w-full pl-10 {{ $term !=='' ?'pr-9' :'pr-4' }} py-2 text-sm rounded-full bg-[var(--surface)] border border-[var(--border-color)] text-[var(--primary)] placeholder-[var(--text-muted)] outline-none focus:border-[var(--primary)] transition shadow-xs">
                @if($term !== '')
                    <a href="{{ route('search.index', ['tab' => $tab]) }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--primary)] transition p-0.5 rounded-full hover:bg-[var(--surface-hover)]"
                       aria-label="Hapus kata kunci">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabs Row (Populer, Terbaru, Orang, Karya, Komunitas) -->
        <div class="flex items-center overflow-x-auto scrollbar-none border-t border-[var(--border-color)] text-sm font-medium">
            @php
                $searchTabs = [
                    'populer' => 'Populer',
                    'terbaru' => 'Terbaru',
                    'orang' => 'Orang',
                    'karya' => 'Karya',
                    'komunitas' => 'Komunitas',
                ];
            @endphp
            @foreach($searchTabs as $tabKey => $tabLabel)
                @php $isActive = $tab === $tabKey; @endphp
                <a href="{{ route('search.index', array_filter(['q' => $term, 'tab' => $tabKey])) }}"
                   class="flex-1 min-w-[85px] sm:min-w-0 text-center py-3 px-2 sm:px-3 whitespace-nowrap transition-colors relative {{ $isActive ? 'text-[var(--primary)] font-bold' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/50 ' }}">
                    <span>{{ $tabLabel }}</span>
                    @if($isActive)
                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-full"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- SEARCH RESULTS AREA -->
    <div class="pt-1">
        @if($term === '')
            <!-- Empty Query State -->
            <x-empty-state
                title="Cari karya, akun, atau komunitas"
                description="Temukan project inovatif dari developer Indonesia, ikuti kreator bertalenta, atau bergabung ke komunitas teknologi.">
                <x-slot:icon>
                    <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            @if($tab === 'populer')
                @php
                    $hasAnyPopulerResult = $people->isNotEmpty() || $projects->isNotEmpty() || $communities->isNotEmpty();
                @endphp

                @if(! $hasAnyPopulerResult)
                    <x-empty-state
                        title="Tidak ada hasil untuk &quot;{{ $term }}&quot;"
                        description="Coba periksa ejaan kata kunci atau cari dengan istilah teknologi, nama developer, atau kategori lain.">
                        <x-slot:icon>
                            <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </x-slot:icon>
                    </x-empty-state>
                @else
                    <div class="space-y-6">
                        {{-- Section: Orang --}}
                        @if($people->isNotEmpty())
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between px-1">
                                    <h2 class="text-base font-bold text-[var(--primary)]">Orang</h2>
                                    <a href="{{ route('search.index', ['q' => $term, 'tab' => 'orang']) }}"
                                       class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Lihat semua
                                    </a>
                                </div>
                                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl divide-y divide-[var(--border-color)] overflow-hidden shadow-xs">
                                    @foreach($people as $person)
                                        <div class="p-4 flex items-start justify-between gap-3 hover:bg-[var(--surface-hover)]/40 transition-colors">
                                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                                <a href="{{ route('profile.show', $person->username) }}" class="shrink-0 mt-0.5">
                                                    <x-user-avatar :user="$person" size="sm" />
                                                </a>
                                                <div class="min-w-0 flex-1">
                                                    <a href="{{ route('profile.show', $person->username) }}" class="font-bold text-sm text-[var(--primary)] hover:underline transition truncate block">
                                                        {{ $person->name }}
                                                    </a>
                                                    <p class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $person->username }}</p>
                                                    @if($person->bio)
                                                        <p class="text-xs text-[var(--primary-hover)] line-clamp-2 mt-1 leading-relaxed">{{ $person->bio }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="shrink-0 pt-0.5">
                                                <x-follow-button :user="$person" size="sm" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Section: Karya --}}
                        @if($projects->isNotEmpty())
                            <div class="space-y-3">
                                <div class="flex items-center justify-between px-1">
                                    <h2 class="text-base font-bold text-[var(--primary)]">Karya</h2>
                                    <a href="{{ route('search.index', ['q' => $term, 'tab' => 'karya']) }}"
                                       class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Lihat semua
                                    </a>
                                </div>
                                <div class="space-y-4">
                                    @foreach($projects as $project)
                                        <x-project-card
                                            :project="$project"
                                            :initial-vote="$userVotes[$project->id] ?? null"
                                            :is-bookmarked="in_array($project->id, $userBookmarkedIds)"
                                            :is-reposted="in_array($project->id, $userRepostedIds)" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Section: Komunitas --}}
                        @if($communities->isNotEmpty())
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between px-1">
                                    <h2 class="text-base font-bold text-[var(--primary)]">Komunitas</h2>
                                    <a href="{{ route('search.index', ['q' => $term, 'tab' => 'komunitas']) }}"
                                       class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Lihat semua
                                    </a>
                                </div>
                                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl divide-y divide-[var(--border-color)] overflow-hidden shadow-xs">
                                    @foreach($communities as $community)
                                        <a href="{{ route('communities.show', $community->slug) }}"
                                           class="p-4 flex items-center justify-between gap-3 hover:bg-[var(--surface-hover)]/40 transition-colors group">
                                           <div class="min-w-0 flex-1">
                                               <div class="flex items-center gap-2">
                                                   <h3 class="font-bold text-sm text-[var(--primary)] group-hover:underline truncate">
                                                       {{ $community->name }}
                                                   </h3>
                                                   @if($community->topic)
                                                       <span class="px-2 py-0.5 rounded-md text-[11px] font-mono bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0">
                                                           #{{ $community->topic }}
                                                       </span>
                                                   @endif
                                               </div>
                                               <p class="text-xs text-[var(--text-muted)] mt-1">
                                                   {{ number_format($community->members_count) }} anggota
                                                   @if($community->description)
                                                       <span>&bull;</span>
                                                       <span class="line-clamp-1 inline">{{ $community->description }}</span>
                                                   @endif
                                               </p>
                                           </div>
                                           <span class="text-xs font-medium text-[#6E6D67] group-hover:text-[var(--primary)] shrink-0">&rarr;</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            @elseif($tab === 'terbaru' || $tab === 'karya')
                @if($projects->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($projects as $project)
                            <x-project-card
                                :project="$project"
                                :initial-vote="$userVotes[$project->id] ?? null"
                                :is-bookmarked="in_array($project->id, $userBookmarkedIds)"
                                :is-reposted="in_array($project->id, $userRepostedIds)" />
                        @endforeach
                    </div>

                    @if($projects instanceof \Illuminate\Pagination\LengthAwarePaginator && $projects->hasPages())
                        <div class="pt-6">
                            {{ $projects->links() }}
                        </div>
                    @endif
                @else
                    <x-empty-state
                        title="Tidak ada karya untuk &quot;{{ $term }}&quot;"
                        description="Coba cari dengan kata kunci teknologi, framework, atau judul yang berbeda.">
                        <x-slot:icon>
                            <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </x-slot:icon>
                    </x-empty-state>
                @endif

            @elseif($tab === 'orang')
                @if($people->isNotEmpty())
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl divide-y divide-[var(--border-color)] overflow-hidden shadow-xs">
                        @foreach($people as $person)
                            <div class="p-4 sm:p-5 flex items-start justify-between gap-3 hover:bg-[var(--surface-hover)]/40 transition-colors">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <a href="{{ route('profile.show', $person->username) }}" class="shrink-0 mt-0.5">
                                        <x-user-avatar :user="$person" size="sm" />
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('profile.show', $person->username) }}" class="font-bold text-sm sm:text-base text-[var(--primary)] hover:underline transition truncate block">
                                            {{ $person->name }}
                                        </a>
                                        <p class="text-xs font-mono text-[var(--text-muted)] truncate">&#64;{{ $person->username }}</p>
                                        @if($person->bio)
                                            <p class="text-xs sm:text-sm text-[var(--primary-hover)] line-clamp-2 mt-1.5 leading-relaxed">{{ $person->bio }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0 pt-0.5">
                                    <x-follow-button :user="$person" size="sm" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($people instanceof \Illuminate\Pagination\LengthAwarePaginator && $people->hasPages())
                        <div class="pt-6">
                            {{ $people->links() }}
                        </div>
                    @endif
                @else
                    <x-empty-state
                        title="Tidak ada developer untuk &quot;{{ $term }}&quot;"
                        description="Coba cari dengan nama lengkap atau username yang berbeda.">
                        <x-slot:icon>
                            <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </x-slot:icon>
                    </x-empty-state>
                @endif

            @elseif($tab === 'komunitas')
                @if($communities->isNotEmpty())
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl divide-y divide-[var(--border-color)] overflow-hidden shadow-xs">
                        @foreach($communities as $community)
                            <a href="{{ route('communities.show', $community->slug) }}"
                               class="p-4 sm:p-5 flex items-center justify-between gap-3 hover:bg-[var(--surface-hover)]/40 transition-colors group">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-sm sm:text-base text-[var(--primary)] group-hover:underline truncate">
                                            {{ $community->name }}
                                        </h3>
                                        @if($community->topic)
                                            <span class="px-2 py-0.5 rounded-md text-[11px] font-mono bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)] shrink-0">
                                                #{{ $community->topic }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">
                                        {{ number_format($community->members_count) }} anggota
                                        @if($community->description)
                                            <span>&bull;</span>
                                            <span class="line-clamp-1 inline">{{ $community->description }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs font-medium text-[#6E6D67] group-hover:text-[var(--primary)] shrink-0">&rarr;</span>
                            </a>
                        @endforeach
                    </div>

                    @if($communities instanceof \Illuminate\Pagination\LengthAwarePaginator && $communities->hasPages())
                        <div class="pt-6">
                            {{ $communities->links() }}
                        </div>
                    @endif
                @else
                    <x-empty-state
                        title="Tidak ada komunitas untuk &quot;{{ $term }}&quot;"
                        description="Coba cari dengan topik teknologi atau nama komunitas lain.">
                        <x-slot:icon>
                            <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </x-slot:icon>
                    </x-empty-state>
                @endif
            @endif
        @endif
    </div>

</div>
@endsection
