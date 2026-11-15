@props([
    'project',
    'isOwner' => false,
])

<div class="flex items-center justify-between gap-3">
    <div class="flex items-center gap-2.5 min-w-0">
        @if($project->user)
            <a href="{{ route('profile.show', $project->user->username) }}"
               class="shrink-0 hover:opacity-85 transition"
               title="{{ $project->user->name }}"
               @click.stop>
                <x-user-avatar :user="$project->user" size="md" class="!w-9 !h-9" />
            </a>
            <div class="min-w-0 leading-tight">
                <div class="flex items-center gap-1.5 min-w-0">
                    <a href="{{ route('profile.show', $project->user->username) }}"
                       class="text-[13.5px] font-semibold text-[var(--primary)] hover:underline truncate"
                       @click.stop>
                        {{ $project->user->name }}
                    </a>
                    <a href="{{ route('profile.show', $project->user->username) }}"
                       class="text-[12px] font-mono text-[var(--text-muted)] hover:text-[var(--primary)] truncate hidden sm:inline"
                       @click.stop>
                        &#64;{{ $project->user->username }}
                    </a>
                </div>
                <div class="flex items-center gap-1.5 text-[11.5px] text-[var(--text-muted)] mt-0.5 truncate">
                    <span>{{ $project->created_at->diffForHumans() }}</span>
                    @if($project->category)
                        <span class="text-[var(--text-faint)]">&middot;</span>
                        <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}"
                           class="text-emerald-400/90 hover:text-emerald-300 font-medium transition-colors truncate"
                           @click.stop>
                            {{ $project->category->name }}
                        </a>
                    @endif
                </div>
            </div>
        @else
            <x-user-avatar name="Anon" size="md" class="!w-9 !h-9" />
            <div class="min-w-0 leading-tight">
                <span class="block text-[13.5px] font-semibold text-[var(--text-muted)]">Developer Anonim</span>
                <span class="block text-[11.5px] text-[var(--text-muted)] mt-0.5">{{ $project->created_at->diffForHumans() }}</span>
            </div>
        @endif
    </div>

    <div class="flex items-center gap-1.5 shrink-0" @click.stop>
        @if($project->created_at->diffInHours(now()) < 24)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Baru
            </span>
        @endif

        @if($project->isPrivate())
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                Private
            </span>
        @endif

        @if($project->user && auth()->check() && auth()->id() !== $project->user->id)
            <x-follow-button :user="$project->user" size="sm" variant="subtle" />
        @endif

        @if($isOwner)
            <form method="POST" action="{{ route('projects.pin', $project) }}" class="shrink-0" @click.stop>
                @csrf
                <button type="submit"
                        title="{{ $project->is_pinned ? 'Lepas sematan' : 'Sematkan ke profil' }}"
                        class="w-7 h-7 rounded-full flex items-center justify-center transition cursor-pointer {{ $project->is_pinned ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/25' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]' }}">
                    <svg class="w-3.5 h-3.5" fill="{{ $project->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                </button>
            </form>
        @endif
    </div>
</div>
