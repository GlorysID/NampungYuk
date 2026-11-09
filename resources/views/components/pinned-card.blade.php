@props([
    'project',
    'pinned' => false,
    'isOwner' => false,
])

<div class="rounded-xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden hover:border-[var(--border-strong)] transition shadow-xs group relative">
    <!-- Thumbnail -->
    <a href="{{ route('projects.show', $project->slug) }}" class="block relative aspect-video bg-[var(--surface-muted)] overflow-hidden">
        @if($project->thumbnail)
            <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" loading="lazy" decoding="async"
                 class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';">
        @else
            <div class="w-full h-full flex items-center justify-center text-[var(--text-muted)]">
                <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
        @endif

        @if($pinned)
            <span class="absolute top-2 left-2 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-mono font-bold uppercase bg-[#0B0B0D]/90 text-emerald-400 border border-[#242426] backdrop-blur-xs">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                <span>Sematan</span>
            </span>
        @endif
    </a>

    <div class="p-3.5 space-y-2.5">
        <div class="flex items-start justify-between gap-2">
            <a href="{{ route('projects.show', $project->slug) }}" class="font-bold text-sm text-[var(--primary)] leading-snug line-clamp-2 hover:underline transition">
                {{ $project->title }}
            </a>

            @if($isOwner)
                <form method="POST" action="{{ route('projects.pin', $project) }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="{{ $pinned ? 'Lepas sematan' : 'Sematkan ke profil' }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition cursor-pointer {{ $pinned ? 'text-emerald-500 hover:bg-emerald-500/10' : 'text-[#6E6D67] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]' }}">
                        <svg class="w-4 h-4" fill="{{ $pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                    </button>
                </form>
            @endif
        </div>

        <div class="flex items-center gap-2.5 text-xs font-mono text-[var(--text-muted)]">
            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                {{ $project->score }}
            </span>
            <span>&bull;</span>
            <span>{{ $project->views_count }} tayangan</span>
            @if($project->isPrivate())
                <span>&bull;</span>
                <span class="text-amber-600 dark:text-amber-400 font-semibold">Private</span>
            @endif
        </div>
    </div>
</div>
