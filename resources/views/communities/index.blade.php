@extends('layouts.app')

@section('title', 'Jelajah Komunitas — NampungYuk')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Komunitas</h1>
            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Temukan & gabung komunitas developer sesuai minatmu.</p>
        </div>

        @auth
            <button type="button" @click="$refs.createModal.showModal()" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Komunitas</span>
            </button>
        @endauth
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('communities.index') }}" class="relative max-w-md">
        <svg class="w-4 h-4 text-[#63636b] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari komunitas..."
               class="ny-input text-sm pl-10">
    </form>

    <!-- Grid komunitas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($communities as $community)
            @php $joined = in_array($community->id, $joinedIds); @endphp
            <div class="ny-card p-4 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    @if($community->icon)
                        <img src="{{ $community->icon }}" alt="" class="w-11 h-11 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-11 h-11 rounded-xl ny-gradient-bg text-white flex items-center justify-center font-bold shrink-0">{{ mb_substr($community->name, 0, 1) }}</div>
                    @endif
                    <div class="min-w-0">
                        <a href="{{ route('communities.show', $community->slug) }}" class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition truncate block">{{ $community->name }}</a>
                        <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">{{ number_format($community->members_count) }} anggota · {{ $community->posts_count }} postingan</p>
                    </div>
                    @if($community->isPrivate())
                        <span class="ml-auto text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400 shrink-0">Private</span>
                    @endif
                </div>

                <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-2 min-h-[2.5rem]">{{ $community->description ?: 'Belum ada deskripsi.' }}</p>

                <div class="flex items-center gap-2 mt-auto">
                    @auth
                        <button type="button" x-data="communityJoin({ joined: {{ $joined ? 'true' : 'false' }}, url: '{{ route('communities.join', $community) }}' })"
                                @click="toggle()" :disabled="loading"
                                :class="joined ? 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa]' : 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]'"
                                class="flex-1 text-xs font-semibold py-2 rounded-lg transition disabled:opacity-60"
                                x-text="joined ? 'Bergabung' : 'Gabung'"></button>
                    @endauth
                    <a href="{{ route('communities.show', $community->slug) }}" class="btn-secondary text-xs py-2 px-3">Lihat</a>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <x-empty-state title="Belum ada komunitas" description="Jadilah yang pertama membuat komunitas developer!" />
            </div>
        @endforelse
    </div>

    @if($communities->hasPages())
        <div>{{ $communities->links() }}</div>
    @endif

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
