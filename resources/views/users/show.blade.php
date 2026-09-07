@extends('layouts.app')

@section('title', $user->name . ' (@' . $user->username . ') — NampungYuk')
@section('meta_description', $user->bio ?: 'Lihat karya codingan dan aktivitas ' . $user->name . ' di NampungYuk.')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'projects' }">

    <!-- Back to Feed Link -->
    <a href="{{ route('projects.index') }}" 
       class="inline-flex items-center gap-1.5 text-xs font-mono text-neutral-500 dark:text-neutral-400 hover:text-orange-500 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Feed</span>
    </a>

    <!-- Profile Overview Card (Vercel / Supabase Style) -->
    <div class="spotlight-card p-6 sm:p-8 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <!-- Avatar -->
            <img src="{{ $user->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($user->name) }}" 
                 alt="{{ $user->name }}" 
                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-neutral-100 dark:bg-[#1a1a1e] border border-neutral-200 dark:border-white/10 object-cover shrink-0 shadow-xs">

            <!-- Info & Bio -->
            <div class="space-y-2 min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">
                            {{ $user->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 font-mono">
                            &#64;{{ $user->username }}
                        </p>
                    </div>

                    @if($user->github_url)
                        <a href="{{ $user->github_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-white/[0.04] hover:bg-neutral-200 dark:hover:bg-white/[0.08] text-neutral-800 dark:text-neutral-200 text-xs font-semibold rounded-xl border border-neutral-200 dark:border-white/[0.08] transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                            <span>GitHub</span>
                        </a>
                    @endif
                </div>

                @if($user->bio)
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed pt-1">
                        {{ $user->bio }}
                    </p>
                @endif

                <!-- Stats Badges -->
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold border border-orange-200 dark:border-orange-500/20 font-mono">
                        <svg class="w-3.5 h-3.5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.63 1.066-.948 1.687a17.067 17.067 0 01-1.393 2.378C6.98 8.444 6 10.373 6 12a6 6 0 1012 0c0-1.63-.787-3.56-1.782-4.887a17.09 17.09 0 00-1.393-2.378 28.09 28.09 0 00-.948-1.687c-.208-.322-.477-.65-.822-.88-.47-.315-1.09-.315-1.56 0z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $user->reputation_points }} Poin Reputasi Dev</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 text-neutral-500 dark:text-neutral-400 font-mono">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span>{{ $totalProjects }} Karya Codingan</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 text-neutral-500 dark:text-neutral-400 font-mono">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Bergabung {{ $user->created_at->translatedFormat('F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Supabase Segmented Style) -->
    <div class="spotlight-card p-1.5 flex items-center gap-1.5 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
        <button @click="activeTab = 'projects'" 
                :class="{ 'bg-neutral-900 text-white dark:bg-[#1f1f23] dark:text-white border border-neutral-300 dark:border-white/[0.14] font-semibold shadow-2xs': activeTab === 'projects', 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/[0.04] border border-transparent': activeTab !== 'projects' }"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            <span>Karya Codingan</span>
            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-md transition-colors"
                  :class="activeTab === 'projects' ? 'bg-neutral-800 dark:bg-white/10 text-neutral-300 dark:text-neutral-200' : 'bg-neutral-200/60 dark:bg-white/[0.05] text-neutral-500'">
                {{ $totalProjects }}
            </span>
        </button>

        <button @click="activeTab = 'comments'" 
                :class="{ 'bg-neutral-900 text-white dark:bg-[#1f1f23] dark:text-white border border-neutral-300 dark:border-white/[0.14] font-semibold shadow-2xs': activeTab === 'comments', 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/[0.04] border border-transparent': activeTab !== 'comments' }"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>Review & Diskusi</span>
            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-md transition-colors"
                  :class="activeTab === 'comments' ? 'bg-neutral-800 dark:bg-white/10 text-neutral-300 dark:text-neutral-200' : 'bg-neutral-200/60 dark:bg-white/[0.05] text-neutral-500'">
                {{ $comments->count() }}
            </span>
        </button>
    </div>

    <!-- Tab 1: Karya Codingan -->
    <div x-show="activeTab === 'projects'" class="space-y-4">
        @forelse($projects as $project)
            <article class="spotlight-card p-5 sm:p-6 space-y-3 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                <div class="flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-white/[0.04] text-neutral-700 dark:text-neutral-300 font-mono border border-neutral-200 dark:border-white/[0.08]">
                        <span>{{ $project->category->name }}</span>
                    </span>
                    <span class="text-xs text-neutral-400 font-mono">
                        {{ $project->created_at->diffForHumans() }}
                    </span>
                </div>

                <a href="{{ route('projects.show', $project->slug) }}" class="block group">
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white group-hover:text-orange-500 transition">
                        {{ $project->title }}
                    </h3>
                </a>

                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    {{ $project->tagline }}
                </p>

                @if($project->thumbnail)
                    <div class="bg-neutral-950 rounded-xl overflow-hidden max-h-[300px] border border-neutral-200 dark:border-white/[0.08]">
                        <a href="{{ route('projects.show', $project->slug) }}">
                            <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-full h-auto max-h-[300px] object-cover hover:opacity-95 transition">
                        </a>
                    </div>
                @endif

                <!-- Tech Stacks -->
                @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
                    <div class="flex flex-wrap gap-1.5 font-mono pt-1">
                        @foreach($project->tech_stacks as $tech)
                            <span class="linear-pill">
                                #{{ $tech }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <!-- Stats & Links -->
                <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-white/[0.06] text-xs">
                    <div class="flex items-center gap-3 font-mono">
                        <span class="inline-flex items-center gap-1 font-bold text-orange-500">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $project->score }} Poin</span>
                        </span>
                        <span class="inline-flex items-center gap-1 text-neutral-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>{{ $project->comments_count }}</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($project->demo_url)
                            <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-semibold rounded-lg hover:bg-emerald-100 transition text-xs font-mono">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Demo</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif
                        @if($project->github_url)
                            <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-neutral-100 dark:bg-white/[0.04] text-neutral-800 dark:text-neutral-200 font-semibold rounded-lg hover:bg-neutral-200 dark:hover:bg-white/[0.08] transition text-xs font-mono">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                </svg>
                                <span>Repo</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="spotlight-card p-12 text-center space-y-2 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                <svg class="w-8 h-8 text-neutral-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Belum ada karya codingan yang dipamerkan</p>
                <p class="text-xs text-neutral-500">Developer ini belum mempublikasikan hasil build kodingannya.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $projects->links() }}
        </div>
    </div>

    <!-- Tab 2: Aktivitas Diskusi & Review -->
    <div x-show="activeTab === 'comments'" x-cloak class="space-y-3">
        @forelse($comments as $comment)
            <div class="spotlight-card p-4 space-y-2 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-neutral-500 dark:text-neutral-400">
                        Mereview karya codingan:
                        @if($comment->project)
                            <a href="{{ route('projects.show', $comment->project->slug) }}" class="font-bold text-neutral-900 dark:text-white hover:text-orange-500 transition">
                                {{ $comment->project->title }}
                            </a>
                        @else
                            <span class="italic text-neutral-400">Proyek telah dihapus</span>
                        @endif
                    </span>
                    <span class="text-neutral-400 text-[11px] font-mono">
                        {{ $comment->created_at->diffForHumans() }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line p-3 rounded-xl bg-neutral-50 dark:bg-[#111114] border border-neutral-200/60 dark:border-white/[0.06] font-mono">
                    {{ $comment->content }}
                </p>
            </div>
        @empty
            <div class="spotlight-card p-12 text-center space-y-2 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                <svg class="w-8 h-8 text-neutral-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Belum ada aktivitas review codingan</p>
                <p class="text-xs text-neutral-500">Developer ini belum menulis review teknis atau komentar di karya lain.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
