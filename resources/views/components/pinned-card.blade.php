@props([
    'project',
    'pinned' => false,
    'isOwner' => false,
])

<div class="hl-panel ny-card--interactive group relative overflow-hidden">
    <!-- Thumbnail -->
    <a href="{{ route('projects.show', $project->slug) }}" class="block relative">
        @if($project->thumbnail)
            <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" loading="lazy" decoding="async"
                 class="w-full aspect-video object-cover"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';">
        @else
            <div class="w-full aspect-video bg-[#e6eaee] dark:bg-[#1e2530] flex items-center justify-center text-[#0e9c8b] dark:text-[#50d2c1]">
                <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
        @endif

        @if($pinned)
            <span class="absolute top-2 left-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase bg-[#0b0e11]/85 text-[#50d2c1] backdrop-blur-sm">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4z"/></svg>
            </span>
        @endif
    </a>

    <div class="p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('projects.show', $project->slug) }}" class="font-bold text-xs text-[#10161f] dark:text-[#eaecf0] leading-snug line-clamp-2 hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition">
                {{ $project->title }}
            </a>

            @if($isOwner)
                <form method="POST" action="{{ route('projects.pin', $project) }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="{{ $pinned ? 'Lepas sematan' : 'Sematkan ke profil' }}"
                            class="w-7 h-7 rounded-md flex items-center justify-center transition {{ $pinned ? 'text-[#0e9c8b] dark:text-[#50d2c1] hover:bg-[#50d2c1]/10' : 'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c]' }}">
                        <svg class="w-3.5 h-3.5" fill="{{ $pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                    </button>
                </form>
            @endif
        </div>

        <div class="flex items-center gap-2 text-[10px] font-mono text-[#5c6979] dark:text-[#7e8a9a]">
            <span class="inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                {{ $project->score }}
            </span>
            <span>·</span>
            <span>{{ $project->views_count }} views</span>
            @if($project->isPrivate())
                <span>·</span>
                <span class="text-amber-600 dark:text-amber-400">Private</span>
            @endif
        </div>
    </div>
</div>
