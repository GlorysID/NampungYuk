@extends('layouts.app')

@section('title', 'Notifikasi — NampungYuk')
@section('header_title', 'Notifikasi')

@section('content')
@php
    $typeMeta = [
        'project_comment' => ['label' => 'Komentar', 'group' => 'interaksi', 'badgeBg' => 'bg-sky-500 text-zinc-950'],
        'project_comment_reply' => ['label' => 'Balasan', 'group' => 'interaksi', 'badgeBg' => 'bg-sky-500 text-zinc-950'],
        'community_comment' => ['label' => 'Diskusi', 'group' => 'komunitas', 'badgeBg' => 'bg-sky-500 text-zinc-950'],
        'community_post' => ['label' => 'Komunitas', 'group' => 'komunitas', 'badgeBg' => 'bg-emerald-500 text-zinc-950'],
        'project_uploaded' => ['label' => 'Karya Baru', 'group' => 'karya', 'badgeBg' => 'bg-[var(--primary)] text-[var(--primary-text)]'],
        'project_reposted' => ['label' => 'Repost', 'group' => 'karya', 'badgeBg' => 'bg-emerald-500 text-zinc-950'],
        'project_trending' => ['label' => 'Trending', 'group' => 'karya', 'badgeBg' => 'bg-emerald-500 text-zinc-950'],
        'new_follower' => ['label' => 'Pengikut', 'group' => 'interaksi', 'badgeBg' => 'bg-violet-500 text-white'],
        'new_message' => ['label' => 'Pesan', 'group' => 'interaksi', 'badgeBg' => 'bg-amber-500 text-zinc-950'],
    ];

    $filterTabs = [
        'semua' => 'Semua',
        'interaksi' => 'Interaksi',
        'karya' => 'Karya & Tren',
        'komunitas' => 'Komunitas',
    ];

    $groupCounts = [
        'semua' => $notifications->count(),
        'interaksi' => 0,
        'karya' => 0,
        'komunitas' => 0,
    ];

    foreach ($notifications as $n) {
        $g = $typeMeta[$n->data['type'] ?? '']['group'] ?? 'interaksi';
        if (isset($groupCounts[$g])) {
            $groupCounts[$g]++;
        }
    }
@endphp

<div class="space-y-5" x-data="{ activeFilter: 'semua', counts: @js($groupCounts) }">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--primary)]">Notifikasi</h1>
            <span class="text-[11px] font-mono px-2.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                {{ $notifications->total() }} riwayat
            </span>
        </div>

        @if($notifications->count() > 0)
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach($filterTabs as $key => $label)
                    <button type="button"
                            @click="activeFilter = '{{ $key }}'"
                            :class="activeFilter === '{{ $key }}'
                                ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold'
                                : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition cursor-pointer">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Notifications List -->
    @if($notifications->count() > 0)
        <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl divide-y divide-[var(--border-color)] overflow-hidden">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'info';
                    $meta = $typeMeta[$type] ?? ['label' => 'Aktivitas', 'group' => 'interaksi', 'badgeBg' => 'bg-[var(--surface-muted)] text-[var(--primary)]'];
                    $excerpt = $data['comment_excerpt'] ?? $data['post_excerpt'] ?? null;
                    $isFreshUnread = is_null($notification->read_at);
                    $hasActor = !empty($data['actor_name']) || !empty($data['actor_avatar']);
                @endphp

                <a href="{{ $data['url'] ?? '#' }}"
                   x-show="activeFilter === 'semua' || activeFilter === '{{ $meta['group'] }}'"
                   class="group flex items-start gap-3.5 px-4 sm:px-5 py-4 transition-colors hover:bg-[var(--surface-hover)] {{ $isFreshUnread ? 'bg-emerald-500/[0.04]' : '' }}">

                    <!-- Avatar + Type Badge -->
                    <div class="relative shrink-0 mt-0.5">
                        @if($hasActor)
                            <x-user-avatar :name="$data['actor_name'] ?? 'Seseorang'" :avatar="$data['actor_avatar'] ?? null" size="md" />
                            <span class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full ring-2 ring-[var(--surface)] flex items-center justify-center {{ $meta['badgeBg'] }}">
                                @if(in_array($type, ['project_comment', 'project_comment_reply', 'community_comment'], true))
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                @elseif($type === 'project_reposted')
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                @elseif($type === 'project_uploaded')
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                @elseif($type === 'community_post')
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @elseif($type === 'new_message')
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                @endif
                            </span>
                        @else
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Main Body -->
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-[14px] text-[var(--primary)] leading-snug">
                            {{ $data['message'] ?? 'Ada aktivitas baru' }}
                        </p>

                        @if(!empty($excerpt))
                            <p class="text-[13px] text-[var(--text-muted)] leading-relaxed line-clamp-2 pl-2.5 border-l-2 border-[var(--border-color)]">
                                {{ $excerpt }}
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--text-muted)]">
                            <span class="font-semibold text-[var(--text-soft)]">{{ $meta['label'] }}</span>
                            <span class="text-[var(--text-faint)]">&bull;</span>
                            <span class="font-mono">{{ $notification->created_at->diffForHumans() }}</span>
                            @if($isFreshUnread)
                                <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Baru</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Optional Thumbnail + Chevron -->
                    <div class="shrink-0 flex items-center gap-2.5 self-center">
                        @if(!empty($data['thumbnail']))
                            <span class="w-11 h-11 rounded-lg overflow-hidden bg-[var(--surface-muted)] hidden sm:block">
                                <img src="{{ $data['thumbnail'] }}" alt="" class="w-full h-full object-cover">
                            </span>
                        @endif
                        <svg class="w-4 h-4 text-[var(--text-faint)] group-hover:text-[var(--primary)] group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach

            <div x-show="counts[activeFilter] === 0" x-cloak class="px-6 py-12 text-center space-y-1.5">
                <p class="text-sm font-semibold text-[var(--primary)]">Tidak ada notifikasi di kategori ini</p>
                <p class="text-xs text-[var(--text-muted)]">Pilih tab "Semua" untuk melihat seluruh riwayat notifikasimu.</p>
            </div>
        </div>
    @else
        <x-empty-state
            title="Belum ada notifikasi"
            description="Ikuti developer lain atau unggah karya untuk mendapat kabar saat ada komentar, repost, atau aktivitas baru."
            action-label="Jelajahi Karya"
            :action-url="route('projects.index')">
            <x-slot:icon>
                <svg class="w-6 h-6 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </x-slot:icon>
        </x-empty-state>
    @endif

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
