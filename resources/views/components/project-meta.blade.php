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
                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[14.5px] text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline transition truncate" @click.stop>
                    {{ $project->user->name }}
                </a>
                <span class="text-[#6E6D67] dark:text-[#8E8D86] font-mono text-[12.5px] truncate">&#64;{{ $project->user->username }}</span>
                <span class="text-[#908F88] dark:text-[#605F5A] text-[12.5px] shrink-0">&middot;</span>
                <span class="text-[#6E6D67] dark:text-[#8E8D86] text-[12.5px] shrink-0">{{ $project->created_at->diffForHumans() }}</span>
                @if($project->category)
                    <span class="text-[#908F88] dark:text-[#605F5A] text-[12.5px] shrink-0">&middot;</span>
                    <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}"
                       class="text-xs font-medium px-2.5 py-0.5 rounded-md bg-[#EFECE6] dark:bg-[#161619] text-[#4A4944] dark:text-[#A8A7A0] border border-[#E2DFD7] dark:border-[#242426] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition"
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
