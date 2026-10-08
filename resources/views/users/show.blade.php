@extends('layouts.app')

@section('title', $user->name . ' (@' . $user->username . ') — NampungYuk')
@section('meta_description', $user->bio ?: 'Lihat karya project codingan dan kontribusi ' . $user->name . ' di NampungYuk.')

@section('content')
@php
    $initialTab = request('tab') === 'likes' && $isOwner ? 'likes'
                : (request('tab') === 'comments' ? 'comments' : 'projects');
@endphp

<div class="space-y-5" x-data="{ activeTab: '{{ $initialTab }}' }">

    <!-- ===================== PROFILE HERO ===================== -->
    <div class="hl-panel overflow-hidden">

        <!-- Cover banner (Hyperliquid gradient mesh, no slop) -->
        <div class="relative h-28 sm:h-36 bg-[#0b0e11] dark:bg-[#0b0e11]">
            <div class="absolute inset-0 opacity-90"
                 style="background:
                    radial-gradient(120% 140% at 0% 0%, rgba(80,210,193,0.35) 0%, transparent 55%),
                    radial-gradient(120% 140% at 100% 0%, rgba(14,156,139,0.28) 0%, transparent 55%),
                    linear-gradient(180deg, rgba(20,24,33,0) 0%, rgba(11,14,17,0.6) 100%);"></div>
            <!-- faint grid overlay for terminal feel -->
            <div class="absolute inset-0 opacity-[0.07]"
                 style="background-image: linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px); background-size: 26px 26px;"></div>
        </div>

        <div class="px-5 sm:px-6 pb-5 -mt-10 sm:-mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <!-- Avatar -->
                <div class="relative shrink-0">
                    <img src="{{ $user->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($user->name) }}"
                         alt="{{ $user->name }}"
                         class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover ring-4 ring-white dark:ring-[#141821] bg-[#e6eaee] dark:bg-[#1e2530]">
                    @if($isOwner)
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-md bg-[#0e9c8b] dark:bg-[#50d2c1] text-[#04110f] flex items-center justify-center ring-2 ring-white dark:ring-[#141821]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @endif
                </div>

                <!-- Name + bio -->
                <div class="flex-1 min-w-0 pb-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-[#10161f] dark:text-[#eaecf0] tracking-tight truncate">
                            {{ $user->name }}
                        </h1>
                        @if($user->is_admin ?? false)
                            <x-badge variant="teal" size="xs">Admin</x-badge>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-[#5c6979] dark:text-[#7e8a9a] font-mono">&#64;{{ $user->username }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0 pb-1">
                    @if($isOwner)
                        <a href="{{ route('profile.edit') }}" class="btn-secondary text-xs py-2 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Profil</span>
                        </a>
                    @else
                        @if($user->github_url)
                            <a href="{{ $user->github_url }}" target="_blank" rel="noopener noreferrer" class="btn-secondary text-xs py-2 px-3">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                <span>GitHub</span>
                            </a>
                        @endif
                        <x-follow-button :user="$user" />
                    @endif
                </div>
            </div>

            @if($user->bio)
                <p class="text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed mt-4 max-w-2xl">{{ $user->bio }}</p>
            @endif

            <!-- ===== STAT TERMINAL BAR (mono, dense, hoverable) ===== -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px mt-5 rounded-lg overflow-hidden border border-[#d5dbe2] dark:border-[#262d3a] bg-[#d5dbe2] dark:bg-[#262d3a]">
                <div class="bg-white dark:bg-[#141821] px-4 py-3">
                    <p class="hl-label">Reputasi</p>
                    <p class="hl-stat text-lg font-bold text-[#0e9c8b] dark:text-[#50d2c1] mt-0.5 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        {{ number_format($user->reputation_points) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-[#141821] px-4 py-3">
                    <p class="hl-label">Karya</p>
                    <p class="hl-stat text-lg font-bold text-[#10161f] dark:text-[#eaecf0] mt-0.5">{{ $totalProjects }}</p>
                </div>
                <div class="bg-white dark:bg-[#141821] px-4 py-3">
                    <p class="hl-label">Pengikut</p>
                    <p class="hl-stat text-lg font-bold text-[#10161f] dark:text-[#eaecf0] mt-0.5">{{ number_format($followersCount) }}</p>
                </div>
                <div class="bg-white dark:bg-[#141821] px-4 py-3">
                    <p class="hl-label">Mengikuti</p>
                    <p class="hl-stat text-lg font-bold text-[#10161f] dark:text-[#eaecf0] mt-0.5">{{ number_format($followingCount) }}</p>
                </div>
            </div>

            <p class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a] mt-3">
                Bergabung {{ $user->created_at->translatedFormat('F Y') }} · {{ number_format($totalUpvotesReceived) }} total upvote diterima
            </p>
        </div>
    </div>

    <!-- ===================== PINNED PROJECTS ===================== -->
    @if($pinnedProjects->count() > 0)
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0e9c8b] dark:text-[#50d2c1]" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                <h2 class="hl-label !text-[#10161f] dark:!text-[#eaecf0]">Disematkan</h2>
                <span class="text-[10px] font-mono text-[#5c6979] dark:text-[#7e8a9a]">{{ $pinnedProjects->count() }}/3</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($pinnedProjects as $project)
                    <x-pinned-card :project="$project" :pinned="true" :is-owner="$isOwner" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- ===================== TABS ===================== -->
    <div class="hl-panel p-1.5 flex items-center gap-1.5">
        <button @click="activeTab = 'projects'"
                :class="activeTab === 'projects' ? 'bg-[#0e9c8b] text-[#04110f] font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            <span>Karya</span>
            <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'projects' ? 'bg-black/15' : 'bg-[#e6eaee] dark:bg-[#1e2530]'">{{ $totalProjects }}</span>
        </button>

        <button @click="activeTab = 'comments'"
                :class="activeTab === 'comments' ? 'bg-[#0e9c8b] text-[#04110f] font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Diskusi</span>
            <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'comments' ? 'bg-black/15' : 'bg-[#e6eaee] dark:bg-[#1e2530]'">{{ $comments->count() }}</span>
        </button>

        @if($isOwner)
            <button @click="activeTab = 'likes'"
                    :class="activeTab === 'likes' ? 'bg-[#0e9c8b] text-[#04110f] font-semibold' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Disukai</span>
                <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'likes' ? 'bg-black/15' : 'bg-[#e6eaee] dark:bg-[#1e2530]'">{{ $totalLikes }}</span>
            </button>
        @endif
    </div>

    <!-- ===================== TAB: PROJECTS ===================== -->
    <div x-show="activeTab === 'projects'" class="space-y-4">
        @forelse($projects as $project)
            @php
                $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                $initialVote = $userVotes[$project->id] ?? null;
            @endphp
            <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-owner="$isOwner" />
        @empty
            <x-empty-state title="Belum ada karya codingan" description="Developer ini belum mempublikasikan project ke NampungYuk." />
        @endforelse

        @if($projects->hasPages())
            <div class="pt-2">{{ $projects->links() }}</div>
        @endif
    </div>

    <!-- ===================== TAB: LIKES (owner only) ===================== -->
    @if($isOwner)
        <div x-show="activeTab === 'likes'" x-cloak class="space-y-4">
            @forelse($likedProjects as $project)
                <x-project-card :project="$project" :initial-vote="'up'" uid="like" />
            @empty
                <x-empty-state title="Belum ada yang disukai" description="Project yang kamu upvote akan muncul di sini." />
            @endforelse

            @if(method_exists($likedProjects, 'hasPages') && $likedProjects->hasPages())
                <div class="pt-2">{{ $likedProjects->links() }}</div>
            @endif
        </div>
    @endif

    <!-- ===================== TAB: COMMENTS ===================== -->
    <div x-show="activeTab === 'comments'" x-cloak class="space-y-3">
        @forelse($comments as $comment)
            <div class="hl-panel p-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    @if($comment->project)
                        <span class="text-[#5c6979] dark:text-[#7e8a9a]">
                            Memberikan masukan di
                            <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" class="font-bold text-[#0e9c8b] dark:text-[#50d2c1] hover:underline">
                                {{ $comment->project->title }}
                            </a>
                        </span>
                    @endif
                    <span class="text-[#5c6979] dark:text-[#7e8a9a] text-[11px] font-mono">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed">"{{ $comment->content }}"</p>
            </div>
        @empty
            <x-empty-state title="Belum ada ulasan" description="Developer ini belum memberikan komentar pada project lain." />
        @endforelse
    </div>

</div>
@endsection
