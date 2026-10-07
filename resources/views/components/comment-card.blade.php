@props([
    'comment',
])

<div class="ny-card p-4 space-y-2 border border-[#d5dbe2] dark:border-[#262d3a]">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5">
            <x-user-avatar :user="$comment->user" :name="$comment->guest_name" size="sm" />
            <div class="flex items-center gap-2 flex-wrap text-xs">
                @if($comment->user)
                    <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[#10161f] dark:text-[#eaecf0] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition">
                        {{ $comment->user->name }}
                    </a>
                    <span class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[#10161f] dark:text-[#eaecf0]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[#5c6979] dark:text-[#7e8a9a] text-[10px]">&bull;</span>
                <span class="text-[11px] text-[#5c6979] dark:text-[#7e8a9a]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <p class="text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed whitespace-pre-line pl-9">
        {{ $comment->content }}
    </p>
</div>
