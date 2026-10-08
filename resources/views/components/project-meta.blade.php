@props([
    'project',
])

<div class="flex items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2.5 min-w-0">
        @if($project->user)
            <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition" title="{{ $project->user->name }}">
                <x-user-avatar :user="$project->user" size="sm" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5 min-w-0">
                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[#18181b] dark:text-[#fafafa] hover:underline transition truncate">
                    {{ $project->user->name }}
                </a>
                <span class="text-[#9096a2] dark:text-[#63636b] text-[11px] shrink-0">&middot;</span>
                <span class="text-[#9096a2] dark:text-[#63636b] text-[11px] shrink-0">{{ $project->created_at->diffForHumans() }}</span>
            </div>
        @else
            <x-user-avatar name="Anon" size="sm" />
            <span class="font-semibold text-[#63636b] dark:text-[#a0a0a0]">Developer Anonim</span>
        @endif
    </div>

    <div class="flex items-center gap-1.5 shrink-0">
        @if($project->user && auth()->check() && auth()->id() !== $project->user->id)
            <x-follow-button :user="$project->user" size="sm" />
        @endif
    </div>
</div>
