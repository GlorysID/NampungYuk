@props([
    'comment',
])

<div class="ny-card p-4 space-y-2 border border-[#DDE5E2] dark:border-[#24322F]">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5">
            <x-user-avatar :user="$comment->user" :name="$comment->guest_name" size="sm" />
            <div class="flex items-center gap-2 flex-wrap text-xs">
                @if($comment->user)
                    <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[#17211F] dark:text-[#F2F5F4] hover:text-[#0F766E] dark:hover:text-teal-400 transition">
                        {{ $comment->user->name }}
                    </a>
                    <span class="text-[11px] font-mono text-[#66736F] dark:text-[#8E9F9B]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[#17211F] dark:text-[#F2F5F4]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[#66736F] dark:text-[#8E9F9B] text-[10px]">&bull;</span>
                <span class="text-[11px] text-[#66736F] dark:text-[#8E9F9B]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <p class="text-xs sm:text-sm text-[#17211F] dark:text-[#F2F5F4] leading-relaxed whitespace-pre-line pl-9">
        {{ $comment->content }}
    </p>
</div>
