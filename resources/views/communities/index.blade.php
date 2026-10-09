@extends('layouts.app')

@section('title', 'Komunitas — NampungYuk')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Komunitas</h1>
            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Postingan & diskusi dari komunitas developer.</p>
        </div>

        @auth
            <button type="button" @click="$refs.createModal.showModal()" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Komunitas</span>
            </button>
        @endauth
    </div>

    <!-- Pill tabs -->
    <div class="hl-panel p-1.5 inline-flex items-center gap-1.5 overflow-x-auto max-w-full">
        @foreach(['untukmu' => 'Untukmu', 'diikuti' => 'Diikuti', 'populer' => 'Populer', 'baru' => 'Baru'] as $key => $label)
            <a href="{{ route('communities.index', array_filter(['tab' => $key, 'q' => $search])) }}"
               class="shrink-0 inline-flex items-center px-3.5 py-2 rounded-md text-xs font-medium transition {{ $tab === $key ? 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- 2-column: feed (left) + communities (right) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN FEED -->
        <div class="xl:col-span-8 space-y-4">
            <form method="GET" action="{{ route('communities.index') }}" class="relative max-w-md">
                @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
                <svg class="w-4 h-4 text-[#63636b] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari postingan komunitas..." class="ny-input text-sm !pl-10">
            </form>

            <div x-data="infiniteFeed({
                    nextPageUrl: {{ $posts->hasMorePages() ? Illuminate\Support\Js::from(route('feeds.communities', request()->query() + ['page' => $posts->currentPage() + 1])) : 'null' }},
                    lastPage: {{ $posts->lastPage() }}
                 })">
                <div class="space-y-3" x-ref="items" data-feed-items>
                    @forelse($posts as $post)
                        <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="false" :can-moderate="false" :show-community="true" />
                    @empty
                        <x-empty-state
                            title="{{ $tab === 'diikuti' ? 'Belum ada postingan dari komunitas yang kamu ikuti' : 'Belum ada postingan' }}"
                            description="Gabung komunitas untuk mulai melihat & membagikan ilmu." />
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

        <!-- RIGHT SIDEBAR: communities -->
        <aside class="xl:col-span-4 space-y-4 xl:sticky xl:top-20">
            <!-- Top Komunitas -->
            <div class="ny-card p-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Top Komunitas</span>
                </h3>
                <div class="space-y-2">
                    @foreach($topCommunities as $i => $c)
                        <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition group">
                            <span class="text-xs font-mono font-bold text-[#63636b] dark:text-[#a0a0a0] w-4 shrink-0">{{ $i + 1 }}</span>
                            @if($c->icon)
                                <img src="{{ $c->icon }}" alt="" class="w-8 h-8 rounded-lg object-cover shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-lg ny-gradient-bg text-white flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">{{ $c->name }}</p>
                                <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]">{{ number_format($c->members_count) }} anggota</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Diikuti -->
            @auth
                @if($followedCommunities->count() > 0)
                    <div class="ny-card p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Diikuti</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach($followedCommunities as $c)
                                <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition group">
                                    @if($c->icon)
                                        <img src="{{ $c->icon }}" alt="" class="w-8 h-8 rounded-lg object-cover shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-lg ny-gradient-bg text-white flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                                    @endif
                                    <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] transition truncate">{{ $c->name }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Jelajah semua komunitas -->
            <div class="ny-card p-4 space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0]">Jelajahi</h3>
                <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">Temukan komunitas baru sesuai minatmu.</p>
                <a href="{{ route('communities.index', ['tab' => 'populer']) }}" class="text-xs font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline inline-flex items-center gap-1">
                    Lihat komunitas populer
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </aside>
    </div>

    <!-- Create modal -->
    @auth
        <dialog x-ref="createModal" class="rounded-2xl p-0 w-full max-w-md backdrop:bg-black/50 bg-transparent">
            <form method="POST" action="{{ route('communities.store') }}" class="ny-card bg-white dark:bg-[#0a0a0a] p-5 space-y-4 text-left">
                @csrf
                <h3 class="font-bold text-sm text-[#18181b] dark:text-[#fafafa]">Buat Komunitas Baru</h3>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Nama Komunitas</label>
                    <input type="text" name="name" required maxlength="60" class="ny-input text-sm" placeholder="mis. Web Dev Indonesia">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Deskripsi</label>
                    <textarea name="description" rows="2" maxlength="280" class="ny-input text-sm resize-none" placeholder="Komunitas ini tentang..."></textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">URL Ikon (opsional)</label>
                    <input type="url" name="icon" class="ny-input text-sm" placeholder="https://...">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Visibilitas</label>
                    <select name="visibility" class="ny-input text-sm">
                        <option value="public">Publik</option>
                        <option value="private">Private</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="$refs.createModal.close()" class="btn-secondary text-xs py-2 px-4">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-2 px-4">Buat</button>
                </div>
            </form>
        </dialog>
    @endauth

</div>
@endsection
