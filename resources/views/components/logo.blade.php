@props([
    'size' => 'md', // 'sm', 'md', 'lg'
    'showText' => true,
    'showTagline' => false,
    'href' => route('projects.index'),
])

@php
    $sizes = [
        'sm' => [
            'icon' => 'w-7 h-7 rounded-lg text-xs',
            'text' => 'text-sm',
            'tagline' => 'text-[9px]',
        ],
        'md' => [
            'icon' => 'w-8 h-8 rounded-lg text-xs',
            'text' => 'text-base',
            'tagline' => 'text-[10px]',
        ],
        'lg' => [
            'icon' => 'w-10 h-10 rounded-xl text-sm',
            'text' => 'text-xl',
            'tagline' => 'text-xs',
        ],
    ][$size] ?? [
        'icon' => 'w-8 h-8 rounded-lg text-xs',
        'text' => 'text-base',
        'tagline' => 'text-[10px]',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 group select-none']) }}>
    @if($href)
        <a href="{{ $href }}" class="flex items-center gap-2.5">
    @else
        <div class="flex items-center gap-2.5">
    @endif

        <!-- Icon Badge -->
        <div class="{{ $sizes['icon'] }} bg-[#0F766E] hover:bg-[#115E59] text-white flex items-center justify-center font-mono font-bold shadow-xs transition shrink-0">
            <span>{;}</span>
        </div>

        @if($showText)
            <div class="flex flex-col">
                <span class="{{ $sizes['text'] }} font-extrabold tracking-tight text-[#17211F] dark:text-[#F2F5F4] leading-none flex items-center gap-0.5">
                    Nampung<span class="text-[#0F766E] dark:text-teal-400">Yuk</span>
                </span>
                @if($showTagline)
                    <span class="{{ $sizes['tagline'] }} font-mono uppercase tracking-wider text-[#66736F] dark:text-[#8E9F9B] mt-0.5">
                        Dev Showcase
                    </span>
                @endif
            </div>
        @endif

    @if($href)
        </a>
    @else
        </div>
    @endif
</div>
