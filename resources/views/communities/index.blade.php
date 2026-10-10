@extends('layouts.app')

@section('title', 'Komunitas — NampungYuk')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Komunitas</h1>
            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Temukan & gabung komunitas developer sesuai minatmu. Berbagi ilmu, tanya jawab, dan kolaborasi.</p>
        </div>
        @auth
            <button type="button" @click="$refs.createModal.showModal()" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5 shrink-0">
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

    <!-- 2-column: community grid + right sidebar -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- MAIN: community cards -->
        <div class="xl:col-span-8 space-y-4">
            <form method="GET" action="{{ route('communities.index') }}" class="relative max-w-md">
                @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
                <svg class="w-4 h-4 text-[#63636b] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari komunitas (nama, topik)..." class="ny-input text-sm !pl-10">
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($communities as $community)
                    @php $joined = in_array($community->id, $joinedIds); @endphp
                    <div class="ny-card p-4 flex flex-col gap-3">
                        <div class="flex items-start gap-3">
                            @if($community->icon)
                                <img src="{{ $community->icon }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl ny-gradient-bg text-white flex items-center justify-center font-bold text-lg shrink-0">{{ mb_substr($community->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('communities.show', $community->slug) }}" class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition truncate block">{{ $community->name }}</a>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    @if($community->topic)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#e6f0ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#47a8ff] border border-[#3291ff]/25">{{ $community->topic }}</span>
                                    @endif
                                    @if($community->isPrivate())
                                        <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400">Private</span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400">Publik</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-3 min-h-[3rem]">
                            {{ $community->description ?: 'Belum ada deskripsi komunitas.' }}
                        </p>

                        <div class="flex items-center gap-3 text-[11px] text-[#63636b] dark:text-[#a0a0a0] font-mono">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ number_format($community->members_count) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ number_format($community->posts_count) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mt-auto">
                            @auth
                                <button type="button" x-data="communityJoin({ joined: {{ $joined ? 'true' : 'false' }}, url: '{{ route('communities.join', $community) }}' })"
                                        @click="toggle()" :disabled="loading"
                                        :class="joined ? 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa]' : 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]'"
                                        class="flex-1 text-xs font-semibold py-2 rounded-lg transition disabled:opacity-60"
                                        x-text="joined ? 'Bergabung' : 'Gabung'"></button>
                            @endauth
                            <a href="{{ route('communities.show', $community->slug) }}" class="btn-secondary text-xs py-2 px-3 {{ auth()->check() ? '' : 'flex-1 text-center' }}">Lihat Komunitas</a>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2">
                        <x-empty-state title="Belum ada komunitas" description="Jadilah yang pertama membuat komunitas developer!" />
                    </div>
                @endforelse
            </div>

            @if($communities->hasPages())
                <div>{{ $communities->links() }}</div>
            @endif
        </div>

        <!-- RIGHT SIDEBAR -->
        <aside class="xl:col-span-4 space-y-4 xl:sticky xl:top-20">
            <div class="ny-card p-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Top Komunitas</span>
                </h3>
                <div class="space-y-2">
                    @foreach($topCommunities as $i => $c)
                        <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
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

            @auth
                @if($followedCommunities->count() > 0)
                    <div class="ny-card p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Diikuti</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach($followedCommunities as $c)
                                <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
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
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Topik</label>
                    <input type="text" name="topic" maxlength="60" class="ny-input text-sm" placeholder="mis. Programming, AI, Mobile">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Deskripsi (tentang apa)</label>
                    <textarea name="description" rows="3" maxlength="500" class="ny-input text-sm resize-none" placeholder="Komunitas ini dibuat untuk... Siapa yang cocok bergabung..."></textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Aturan (opsional)</label>
                    <textarea name="rules" rows="2" maxlength="1000" class="ny-input text-sm resize-none" placeholder="1. Saling menghormati&#10;2. Relevan dengan topik"></textarea>
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
