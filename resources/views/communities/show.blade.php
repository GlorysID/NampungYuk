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
                            <img src="{{ $m->avatar ?: 'https://randomuser.me/api/portraits/'.(crc32($m->name) % 2 === 0 ? 'men' : 'women').'/'.(crc32($m->name) % 99 + 1).'.jpg' }}" alt="{{ $m->name }}" class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-[#0a0a0a] object-cover">
                        @endforeach
                    </div>
                    <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">+{{ max(0, $community->members_count - 6) }} lainnya</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Post composer (members only) -->
    @auth
        @if($isMember)
            <form method="POST" action="{{ route('communities.post', $community) }}" enctype="multipart/form-data" class="ny-card p-4 space-y-3">
                @csrf
                <div class="flex gap-3">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                    <textarea name="content" required maxlength="2000" rows="2" placeholder="Tulis sesuatu untuk {{ $community->name }}..."
                              class="ny-input text-sm resize-none flex-1"></textarea>
                </div>
                <div class="flex items-center justify-between pl-10">
                    <label class="inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] cursor-pointer hover:text-[#0070f3] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Foto</span>
                        <input type="file" name="images[]" accept="image/*" multiple class="hidden">
                    </label>
                    <button type="submit" class="btn-primary text-xs py-2 px-4">Posting</button>
                </div>
            </form>
        @else
            <div class="hl-panel p-4 text-center text-xs text-[#63636b] dark:text-[#a0a0a0]">
                Gabung dulu untuk bisa memposting di komunitas ini.
            </div>
        @endif
    @endauth

    <!-- Community feed -->
    <div class="space-y-3">
        @forelse($posts as $post)
            <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="$isMember" />
        @empty
            <x-empty-state title="Belum ada postingan" description="Jadilah yang pertama memulai diskusi di komunitas ini." />
        @endforelse

        @if($posts->hasPages())
            <div>{{ $posts->links() }}</div>
        @endif
    </div>

</div>
@endsection
