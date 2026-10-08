@props([
    'comment',
])

<div class="ny-card p-4 space-y-2 border border-[#e4e4e7] dark:border-[#1f1f1f]">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5">
            <x-user-avatar :user="$comment->user" :name="$comment->guest_name" size="sm" />
            <div class="flex items-center gap-2 flex-wrap text-xs">
                @if($comment->user)
                    <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition">
                        {{ $comment->user->name }}
                    </a>
                    <span class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[#18181b] dark:text-[#fafafa]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[#63636b] dark:text-[#a0a0a0] text-[10px]">&bull;</span>
                <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line pl-9">
        {{ $comment->content }}
    </p>
</div>
