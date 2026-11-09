@props([
    'project',
])

<div class="flex items-center justify-between gap-3 text-sm">
    <div class="flex items-center gap-3 min-w-0">
        @if($project->user)
            <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition" title="{{ $project->user->name }}" @click.stop>
                <x-user-avatar :user="$project->user" size="md" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[14.5px] text-[var(--primary)] hover:underline transition truncate" @click.stop>
                    {{ $project->user->name }}
                </a>
                <span class="text-[var(--text-muted)] font-mono text-[12.5px] truncate">&#64;{{ $project->user->username }}</span>
                <span class="text-[var(--text-faint)] text-[12.5px] shrink-0">&middot;</span>
                <span class="text-[var(--text-muted)] text-[12.5px] shrink-0">{{ $project->created_at->diffForHumans() }}</span>
                @if($project->category)
                    <span class="text-[var(--text-faint)] text-[12.5px] shrink-0">&middot;</span>
                    <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}"
                       class="text-[11.5px] font-medium px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)] hover:text-[var(--primary)] border border-[var(--border-color)] hover:border-[var(--border-strong)] transition-colors"
                       @click.stop>
                        {{ $project->category->name }}
                    </a>
                @endif
            </div>
        @else
            <x-user-avatar name="Anon" size="md" />
            <span class="font-semibold text-zinc-500 dark:text-zinc-400 text-[14.5px]">Developer Anonim</span>
        @endif
    </div>

    <div class="flex items-center gap-1.5 shrink-0" @click.stop>
        @if($project->user && auth()->check() && auth()->id() !== $project->user->id)
            <x-follow-button :user="$project->user" size="sm" />
        @endif
    </div>
</div>
