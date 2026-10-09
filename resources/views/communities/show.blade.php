@extends('layouts.app')

@section('title', $community->name . ' — Komunitas NampungYuk')
@section('meta_description', $community->description ?: 'Komunitas developer di NampungYuk.')

@section('content')
<div class="space-y-5 max-w-3xl mx-auto">

    <!-- Community header -->
    <div class="hl-panel overflow-hidden">
        <div class="h-24 sm:h-32 relative" style="background: linear-gradient(135deg, rgba(0,112,243,0.25), rgba(24,24,27,0.9));">
            @if($community->cover)
                <img src="{{ $community->cover }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            @endif
        </div>
        <div class="px-5 pb-5 -mt-8">
            <div class="flex items-end gap-4">
                @if($community->icon)
                    <img src="{{ $community->icon }}" alt="" class="w-16 h-16 rounded-2xl object-cover ring-4 ring-white dark:ring-[#0a0a0a]">
                @else
                    <div class="w-16 h-16 rounded-2xl ny-gradient-bg text-white flex items-center justify-center text-2xl font-bold ring-4 ring-white dark:ring-[#0a0a0a]">{{ mb_substr($community->name, 0, 1) }}</div>
                @endif
                <div class="flex-1 min-w-0 pb-1">
                    <h1 class="text-lg font-bold text-[#18181b] dark:text-[#fafafa] truncate">{{ $community->name }}</h1>
                    <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">{{ number_format($community->members_count) }} anggota · {{ $community->posts_count }} postingan</p>
                </div>
                @auth
                    <button type="button" x-data="communityJoin({ joined: {{ $isMember ? 'true' : 'false' }}, url: '{{ route('communities.join', $community) }}' })"
                            @click="toggle()" :disabled="loading"
                            :class="joined ? 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa]' : 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]'"
                            class="text-xs font-semibold py-2 px-4 rounded-full transition disabled:opacity-60 shrink-0"
                            x-text="joined ? 'Bergabung' : 'Gabung'"></button>
                @endauth
            </div>

            @if($community->description)
                <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed mt-3">{{ $community->description }}</p>
            @endif

            @if($members->count())
                <div class="flex items-center gap-1.5 mt-3">
                    <div class="flex -space-x-2">
                        @foreach($members->take(6) as $m)
                            <x-user-avatar :user="$m" size="xs" class="ring-2 ring-white dark:ring-[#0a0a0a]!" />
                        @endforeach
                    </div>
                    <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">+{{ max(0, $community->members_count - 6) }} lainnya</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Sort tabs + search -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="hl-panel p-1.5 flex items-center gap-1.5 shrink-0">
            @foreach(['terbaru' => 'Terbaru', 'populer' => 'Terpopuler', 'terjawab' => 'Terjawab'] as $key => $label)
                <a href="{{ route('communities.show', array_filter(['slug' => $community->slug, 'tab' => $key, 'q' => $search])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition {{ $tab === $key ? 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('communities.show', $community->slug) }}" class="relative flex-1">
            @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
            <svg class="w-4 h-4 text-[#63636b] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari di komunitas ini..." class="ny-input text-sm !pl-10">
        </form>
    </div>

    <!-- Post composer (members only) -->
    @auth
        @if($isMember)
            <form method="POST" action="{{ route('communities.post', $community) }}" enctype="multipart/form-data" class="ny-card p-4 space-y-3" x-data="{ type: 'discussion' }">
                @csrf
                <div class="flex gap-3">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                    <textarea name="content" required maxlength="2000" rows="2" placeholder="Bagikan ilmu, ajukan pertanyaan, atau mulai diskusi..."
                              class="ny-input text-sm resize-none flex-1"></textarea>
                </div>
                <input type="hidden" name="type" :value="type">
                <div class="flex flex-wrap items-center justify-between gap-2 pl-10">
                    <div class="flex items-center gap-1.5">
                        @foreach(['discussion' => 'Diskusi', 'question' => 'Tanya Jawab'] as $k => $lbl)
                            <button type="button" @click="type = '{{ $k }}'"
                                    :class="type === '{{ $k }}' ? 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]' : 'bg-[#eeeeef] dark:bg-[#171717] text-[#63636b] dark:text-[#a0a0a0]'"
                                    class="text-[11px] font-semibold px-3 py-1 rounded-full transition">{{ $lbl }}</button>
                        @endforeach
                        @if($isModerator)
                            <button type="button" @click="type = 'announcement'"
                                    :class="type === 'announcement' ? 'bg-violet-600 text-white' : 'bg-[#eeeeef] dark:bg-[#171717] text-[#63636b] dark:text-[#a0a0a0]'"
                                    class="text-[11px] font-semibold px-3 py-1 rounded-full transition">Pengumuman</button>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] cursor-pointer hover:text-[#0070f3] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Foto</span>
                            <input type="file" name="images[]" accept="image/*" multiple class="hidden">
                        </label>
                        <button type="submit" class="btn-primary text-xs py-2 px-4">Posting</button>
                    </div>
                </div>
            </form>
        @else
            <div class="hl-panel p-4 text-center text-xs text-[#63636b] dark:text-[#a0a0a0]">
                Gabung dulu untuk bisa memposting di komunitas ini.
            </div>
        @endif
    @endauth

    <!-- Manage members (owner only) -->
    @if($isOwner && $memberList->count() > 0)
        <div class="ny-card p-4 space-y-3" x-data="{ openMgmt: false }">
            <button type="button" @click="openMgmt = !openMgmt" class="w-full flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Kelola Anggota ({{ $memberList->count() }})</span>
                </h3>
                <svg class="w-4 h-4 text-[#63636b] transition-transform" :class="openMgmt ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="openMgmt" x-cloak class="space-y-1.5 pt-1">
                @foreach($memberList as $m)
                    <div class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition">
                        <x-user-avatar :user="$m" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] truncate">{{ $m->name }}</p>
                            <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $m->username }}</p>
                        </div>
                        @if($m->id === $community->owner_id)
                            <span class="text-[10px] font-bold uppercase text-[#0070f3] dark:text-[#3291ff] shrink-0">Owner</span>
                        @else
                            @if($m->pivot->role === 'mod')
                                <span class="text-[10px] font-bold uppercase text-violet-600 dark:text-violet-400 shrink-0 mr-1">Mod</span>
                            @endif
                            <form method="POST" action="{{ route('communities.moderator', [$community, $m]) }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-semibold px-2.5 py-1 rounded-full border transition shrink-0 {{ $m->pivot->role === 'mod' ? 'border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30' : 'border-[#0070f3]/30 text-[#0070f3] dark:text-[#3291ff] hover:bg-[#e6f0ff] dark:hover:bg-[#3291ff]/10' }}">
                                    {{ $m->pivot->role === 'mod' ? 'Copot' : 'Jadikan Mod' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Community feed (infinite scroll) -->
    <div x-data="infiniteFeed({
            nextPageUrl: {{ $posts->hasMorePages() ? Illuminate\Support\Js::from(route('feeds.community', ['community' => $community->slug] + request()->query() + ['page' => $posts->currentPage() + 1])) : 'null' }},
            lastPage: {{ $posts->lastPage() }}
         })">
        <div class="space-y-3" x-ref="items" data-feed-items>
            @forelse($posts as $post)
                <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="$isMember" :can-moderate="$isModerator" />
            @empty
                <x-empty-state title="Belum ada postingan" description="Jadilah yang pertama memulai diskusi di komunitas ini." />
            @endforelse
        </div>

        <div x-ref="sentinel" class="pt-4 flex justify-center">
            <button type="button" x-show="!done" @click="loadMore()" :disabled="loading" class="btn-secondary text-xs py-2.5 px-5 disabled:opacity-60">
                <span x-show="!loading">Muat lebih banyak</span>
                <span x-show="loading" x-cloak>Memuat...</span>
            </button>
        </div>
    </div>

</div>
@endsection
