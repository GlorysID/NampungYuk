@props([
    'project',
])

<div class="flex items-center justify-between gap-3 text-xs flex-wrap">
    <div class="flex items-center gap-2.5 min-w-0">
        @if($project->user)
            <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition" title="{{ $project->user->name }}">
                <x-user-avatar :user="$project->user" size="sm" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[#10161f] dark:text-[#eaecf0] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition truncate">
                    {{ $project->user->name }}
                </a>
                <span class="text-[#5c6979] dark:text-[#7e8a9a] font-mono text-[11px] truncate">&#64;{{ $project->user->username }}</span>
                <span class="text-[#5c6979] dark:text-[#7e8a9a] text-[10px]">&bull;</span>
                <span class="text-[#5c6979] dark:text-[#7e8a9a] text-[11px]">{{ $project->created_at->diffForHumans() }}</span>
            </div>
        @else
            <x-user-avatar name="Anon" size="sm" />
            <span class="font-semibold text-[#5c6979] dark:text-[#7e8a9a]">Developer Anonim</span>
        @endif
    </div>

    <div class="flex items-center gap-1.5 shrink-0">
        @if($project->user && auth()->check() && auth()->id() !== $project->user->id)
            <x-follow-button :user="$project->user" size="sm" />
        @endif

        @if($project->category)
            <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}" 
               class="text-[11px] font-mono font-medium px-2 py-0.5 rounded-md bg-[#e6eaee] dark:bg-[#1e2530] text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] border border-[#d5dbe2] dark:border-[#262d3a] transition">
                {{ $project->category->name }}
            </a>
        @endif

        @if($project->status)
            @php
                $statusVariants = [
                    'idea' => 'neutral',
                    'prototype' => 'purple',
                    'beta' => 'teal',
                    'production' => 'success',
                    'archived' => 'neutral',
                ];
                $variant = $statusVariants[$project->status] ?? 'neutral';
            @endphp
            <x-badge :variant="$variant" size="xs">
                {{ $project->getStatusLabel() }}
            </x-badge>
        @endif
    </div>
</div>
