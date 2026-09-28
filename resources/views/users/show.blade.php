@extends('layouts.app')

@section('title', $user->name . ' (@' . $user->username . ') — NampungYuk')
@section('meta_description', $user->bio ?: 'Lihat karya project codingan dan kontribusi ' . $user->name . ' di NampungYuk.')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ activeTab: 'projects' }">

    <!-- Back to Feed Link -->
    <a href="{{ route('projects.index') }}" 
       class="inline-flex items-center gap-1.5 text-xs text-[#66736F] dark:text-[#8E9F9B] hover:text-[#0F766E] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Feed</span>
    </a>

    <!-- Profile Overview Card (Portfolio + Community Profile) -->
    <div class="ny-card p-6 sm:p-8 border border-[#DDE5E2] dark:border-[#24322F] bg-white dark:bg-[#151D1B]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <!-- Large Developer Avatar -->
            <x-user-avatar :user="$user" size="xl" />

            <!-- Info & Bio -->
            <div class="space-y-2 min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-[#17211F] dark:text-[#F2F5F4] tracking-tight">
                            {{ $user->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-[#66736F] dark:text-[#8E9F9B] font-mono">
                            &#64;{{ $user->username }}
                        </p>
                    </div>

                    @if($user->github_url)
                        <x-button :href="$user->github_url" target="_blank" rel="noopener noreferrer" variant="secondary" size="sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                            <span>GitHub</span>
                        </x-button>
                    @endif
                </div>

                @if($user->bio)
                    <p class="text-xs sm:text-sm text-[#17211F] dark:text-[#F2F5F4] leading-relaxed pt-1">
                        {{ $user->bio }}
                    </p>
                @endif

                <!-- Stats Badges -->
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-teal-50 dark:bg-teal-950/40 text-[#0F766E] dark:text-teal-300 font-bold border border-teal-200/60 dark:border-teal-800/40 font-mono">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                        </svg>
                        <span>{{ $user->reputation_points }} Poin Reputasi</span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 text-[#66736F] dark:text-[#8E9F9B] font-mono">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span>{{ $totalProjects }} Karya Project</span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 text-[#66736F] dark:text-[#8E9F9B] font-mono">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Bergabung {{ $user->created_at->translatedFormat('F Y') }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="ny-card p-1.5 flex items-center gap-1.5 border border-[#DDE5E2] dark:border-[#24322F] bg-white dark:bg-[#151D1B]">
        <!-- Tab: Projects -->
        <button @click="activeTab = 'projects'" 
                :class="{ 
                    'bg-[#0F766E] text-white font-semibold shadow-2xs': activeTab === 'projects', 
                    'text-[#66736F] dark:text-[#8E9F9B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623]': activeTab !== 'projects' 
                }"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            <span>Karya Codingan</span>
            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded"
                  :class="activeTab === 'projects' ? 'bg-white/20 text-white' : 'bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#66736F]'">
                {{ $totalProjects }}
            </span>
        </button>

        <!-- Tab: Discussion & Reviews -->
        <button @click="activeTab = 'comments'" 
                :class="{ 
                    'bg-[#0F766E] text-white font-semibold shadow-2xs': activeTab === 'comments', 
                    'text-[#66736F] dark:text-[#8E9F9B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623]': activeTab !== 'comments' 
                }"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>Review & Diskusi</span>
            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded"
                  :class="activeTab === 'comments' ? 'bg-white/20 text-white' : 'bg-[#EBF0EE] dark:bg-[#1F2C29] text-[#66736F]'">
                {{ $comments->count() }}
            </span>
        </button>
    </div>

    <!-- TAB 1: Karya Codingan (Primary Focus) -->
    <div x-show="activeTab === 'projects'" class="space-y-4">
        @forelse($projects as $project)
            @php
                $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                $initialVote = $userVotes[$project->id] ?? null;
            @endphp
            <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" />
        @empty
            <x-empty-state 
                title="Belum ada karya codingan" 
                description="Developer ini belum mempublikasikan project ke NampungYuk." />
        @endforelse

        <!-- Pagination -->
        @if($projects->hasPages())
            <div class="pt-4">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 2: Review & Diskusi (Secondary Focus) -->
    <div x-show="activeTab === 'comments'" class="space-y-3">
        @forelse($comments as $comment)
            <div class="ny-card p-4 space-y-2 bg-white dark:bg-[#151D1B] border border-[#DDE5E2] dark:border-[#24322F]">
                <div class="flex items-center justify-between text-xs">
                    @if($comment->project)
                        <span class="text-[#66736F] dark:text-[#8E9F9B]">
                            Memberikan masukan di
                            <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" class="font-bold text-[#0F766E] dark:text-teal-400 hover:underline">
                                {{ $comment->project->title }}
                            </a>
                        </span>
                    @endif
                    <span class="text-[#66736F] text-[11px]">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs sm:text-sm text-[#17211F] dark:text-[#F2F5F4] leading-relaxed">
                    "{{ $comment->content }}"
                </p>
            </div>
        @empty
            <x-empty-state 
                title="Belum ada ulasan" 
                description="Developer ini belum memberikan komentar pada project lain." />
        @endforelse
    </div>

</div>
@endsection
