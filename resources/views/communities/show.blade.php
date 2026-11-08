@extends('layouts.app')

@section('title', $community->name . ' — Komunitas NampungYuk')
@section('header_title', $community->name)
@section('meta_description', $community->description ?: 'Komunitas developer di NampungYuk.')

@section('content')
<div class="space-y-5 max-w-3xl mx-auto">

    <!-- Community header -->
    <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl overflow-hidden shadow-xs">
        <div class="h-24 sm:h-32 relative bg-[#F6F5F0] dark:bg-[#0B0B0D]">
            @if($community->cover)
                <img src="{{ $community->cover }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 bg-gradient-to-r from-stone-200/50 via-stone-100/30 to-stone-200/50 dark:from-[#0B0B0D] dark:via-[#161619] dark:to-[#0B0B0D]"></div>
            @endif
        </div>
        <div class="px-5 pb-5 -mt-8 relative z-10">
            <div class="flex items-end gap-4">
                @if($community->icon)
                    <img src="{{ $community->icon }}" alt="" class="w-16 sm:w-20 h-16 sm:h-20 rounded-2xl object-cover ring-4 ring-white dark:ring-[#121214] bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426]">
                @else
                    <div class="w-16 sm:w-20 h-16 sm:h-20 rounded-2xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] flex items-center justify-center text-2xl font-bold ring-4 ring-white dark:ring-[#121214]">{{ mb_substr($community->name, 0, 1) }}</div>
                @endif
                <div class="flex-1 min-w-0 pb-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-[#0B0B0D] dark:text-[#EAE8E1] truncate">{{ $community->name }}</h1>
                        @if($community->topic)
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] border border-[#E2DFD7] dark:border-[#242426]">{{ $community->topic }}</span>
                        @endif
                        <span class="text-xs font-bold uppercase {{ $community->isPrivate() ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $community->isPrivate() ? 'Private' : 'Publik' }}</span>
                    </div>
                    <p class="text-xs sm:text-sm text-[#6E6D67] dark:text-[#8E8D86] mt-0.5">{{ number_format($community->members_count) }} anggota · {{ $community->posts_count }} postingan · dibuat oleh {{ $community->owner?->name }}</p>
                </div>
                @auth
                    <button type="button" x-data="communityJoin({ joined: {{ $isMember ? 'true' : 'false' }}, url: '{{ route('communities.join', $community) }}' })"
                            @click="toggle()" :disabled="loading"
                            :class="joined ? 'bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]' : 'bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] hover:bg-[#222225] dark:hover:bg-[#DCD9D0]'"
                            class="text-sm font-semibold py-2.5 px-5 rounded-xl transition disabled:opacity-60 shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]"
                            x-text="joined ? 'Bergabung' : 'Gabung'"></button>
                @else
                    <button type="button" @click="window.openAuthModal('login')" class="text-sm font-semibold py-2.5 px-5 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] hover:bg-[#222225] dark:hover:bg-[#DCD9D0] transition shrink-0 cursor-pointer shadow-xs select-none active:scale-[0.98]">
                        Gabung
                    </button>
                @endauth
            </div>

            @if($community->description)
                <div class="mt-4 p-4 rounded-xl bg-[#F6F5F0] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426]">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#6E6D67] dark:text-[#8E8D86] mb-1.5">Tentang komunitas ini</p>
                    <p class="text-sm sm:text-[14.5px] text-[#0B0B0D] dark:text-[#EAE8E1] leading-relaxed whitespace-pre-line">{{ $community->description }}</p>
                    @if($community->rules)
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#6E6D67] dark:text-[#8E8D86] mt-3 mb-1.5">Aturan komunitas</p>
                        <p class="text-xs sm:text-sm text-[#6E6D67] dark:text-[#8E8D86] leading-relaxed whitespace-pre-line">{{ $community->rules }}</p>
                    @endif
                </div>
            @endif

            @if($members->count())
                <div class="flex items-center gap-1.5 mt-3">
                    <div class="flex -space-x-2">
                        @foreach($members->take(6) as $m)
                            <x-user-avatar :user="$m" size="xs" class="ring-2 ring-white dark:ring-[#121214]!" />
                        @endforeach
                    </div>
                    <span class="text-[11px] text-[#6E6D67] dark:text-[#8E8D86]">+{{ max(0, $community->members_count - 6) }} lainnya</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Sort tabs + search -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="inline-flex items-center p-1 rounded-xl bg-[#EFECE6] dark:bg-[#161619] border border-[#E2DFD7] dark:border-[#242426] text-xs shrink-0">
            @foreach(['terbaru' => 'Terbaru', 'populer' => 'Terpopuler', 'terjawab' => 'Terjawab'] as $key => $label)
                <a href="{{ route('communities.show', array_filter(['slug' => $community->slug, 'tab' => $key, 'q' => $search])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === $key ? 'bg-white dark:bg-[#121214] text-[#0B0B0D] dark:text-[#EAE8E1] shadow-xs' : 'text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('communities.show', $community->slug) }}" class="relative flex-1">
            @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
            <svg class="w-4 h-4 text-[#908F88] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari di komunitas ini..." class="w-full pl-10 pr-3.5 py-2 rounded-xl bg-white dark:bg-[#141416] border border-[#E2DFD7] dark:border-[#242426] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#908F88] dark:placeholder:text-[#6E6D67] text-xs sm:text-sm outline-none focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] shadow-xs transition">
        </form>
    </div>

    <!-- Post composer (members only) -->
    @auth
        @if($isMember)
            <form method="POST" action="{{ route('communities.post', $community) }}" enctype="multipart/form-data" class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-4 space-y-3 shadow-xs" x-data="{ type: 'discussion' }">
                @csrf
                <div class="flex gap-3">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                    <textarea name="content" required maxlength="2000" rows="2" placeholder="Bagikan ilmu, ajukan pertanyaan, atau mulai diskusi..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-[#F6F5F0] dark:bg-[#141416] border border-[#E2DFD7] dark:border-[#242426] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#908F88] dark:placeholder:text-[#6E6D67] text-sm resize-none outline-none focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] transition flex-1"></textarea>
                </div>
                <input type="hidden" name="type" :value="type">
                <div class="flex flex-wrap items-center justify-between gap-2 pl-10">
                    <div class="flex items-center gap-1.5">
                        @foreach(['discussion' => 'Diskusi', 'question' => 'Tanya Jawab'] as $k => $lbl)
                            <button type="button" @click="type = '{{ $k }}'"
                                    :class="type === '{{ $k }}' ? 'bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] font-bold shadow-xs' : 'bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1]'"
                                    class="text-[11px] font-medium px-3 py-1 rounded-full transition cursor-pointer">{{ $lbl }}</button>
                        @endforeach
                        @if($isModerator)
                            <button type="button" @click="type = 'announcement'"
                                    :class="type === 'announcement' ? 'bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] font-bold shadow-xs' : 'bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1]'"
                                    class="text-[11px] font-medium px-3 py-1 rounded-full transition cursor-pointer">Pengumuman</button>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs text-[#6E6D67] dark:text-[#8E8D86] cursor-pointer hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Foto</span>
                            <input type="file" name="images[]" accept="image/*" multiple class="hidden">
                        </label>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] hover:bg-[#222225] dark:hover:bg-[#DCD9D0] text-xs font-bold transition shadow-xs cursor-pointer select-none active:scale-[0.98]">Posting</button>
                    </div>
                </div>
            </form>
        @else
            <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-4 text-center text-xs text-[#6E6D67] dark:text-[#8E8D86] shadow-xs">
                Gabung dulu ke komunitas ini untuk mulai memposting atau mengajukan pertanyaan.
            </div>
        @endif
    @endauth

    <!-- Manage members (owner only) -->
    @if($isOwner && $memberList->count() > 0)
        <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl p-4 space-y-3 shadow-xs" x-data="{ openMgmt: false }">
            <button type="button" @click="openMgmt = !openMgmt" class="w-full flex items-center justify-between cursor-pointer">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E6D67] dark:text-[#8E8D86] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#6E6D67] dark:text-[#8E8D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Kelola Anggota ({{ $memberList->count() }})</span>
                </h3>
                <svg class="w-4 h-4 text-[#908F88] transition-transform" :class="openMgmt ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="openMgmt" x-cloak class="space-y-1.5 pt-1">
                @foreach($memberList as $m)
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-[#F6F5F0] dark:hover:bg-[#161619] transition">
                        <x-user-avatar :user="$m" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-xs text-[#0B0B0D] dark:text-[#EAE8E1] truncate">{{ $m->name }}</p>
                            <p class="text-[10px] text-[#6E6D67] dark:text-[#8E8D86] truncate">&#64;{{ $m->username }}</p>
                        </div>
                        @if($m->id === $community->owner_id)
                            <span class="text-[10px] font-bold uppercase text-[#0B0B0D] dark:text-[#EAE8E1] shrink-0">Owner</span>
                        @else
                            @if($m->pivot->role === 'mod')
                                <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400 shrink-0 mr-1">Mod</span>
                            @endif
                            <form method="POST" action="{{ route('communities.moderator', [$community, $m]) }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition shrink-0 cursor-pointer {{ $m->pivot->role === 'mod' ? 'border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30' : 'border-[#E2DFD7] dark:border-[#242426] text-[#0B0B0D] dark:text-[#EAE8E1] hover:bg-[#EFECE6] dark:hover:bg-[#161619]' }}">
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
            <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                    class="px-5 py-2.5 rounded-xl border border-[#E2DFD7] dark:border-[#242426] bg-[#EFECE6] dark:bg-[#161619] hover:bg-[#E2DFD7] dark:hover:bg-[#19191D] text-[#0B0B0D] dark:text-[#EAE8E1] text-xs font-semibold shadow-xs transition disabled:opacity-60 cursor-pointer">
                <span x-show="!loading">Muat lebih banyak</span>
                <span x-show="loading" x-cloak>Memuat...</span>
            </button>
        </div>
    </div>

</div>
@endsection
