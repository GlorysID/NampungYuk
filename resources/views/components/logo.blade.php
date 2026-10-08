@props([
    'size' => 'md', // 'sm', 'md', 'lg'
    'showText' => true,
    'showTagline' => false,
    'href' => route('projects.index'),
])

@php
    $sizes = [
        'sm' => [
            'icon' => 'w-8 h-8 rounded-xl text-[13px]',
            'text' => 'text-[15px]',
            'tagline' => 'text-[9px]',
            'gap' => 'gap-2',
        ],
        'md' => [
            'icon' => 'w-9 h-9 rounded-xl text-sm',
            'text' => 'text-lg',
            'tagline' => 'text-[10px]',
            'gap' => 'gap-2.5',
        ],
        'lg' => [
            'icon' => 'w-12 h-12 rounded-2xl text-lg',
            'text' => 'text-2xl',
            'tagline' => 'text-xs',
            'gap' => 'gap-3',
        ],
    ][$size] ?? [
        'icon' => 'w-9 h-9 rounded-xl text-sm',
        'text' => 'text-lg',
        'tagline' => 'text-[10px]',
        'gap' => 'gap-2.5',
    ];
@endphp

<div {{ $attributes->merge(['class' => "inline-flex items-center {$sizes['gap']} group select-none"]) }}>
    @if($href)
        <a href="{{ $href }}" class="flex items-center {{ $sizes['gap'] }}">
    @else
        <div class="flex items-center {{ $sizes['gap'] }}">
    @endif

        <!-- Gradient icon badge -->
        <div class="{{ $sizes['icon'] }} ny-gradient-bg text-white flex items-center justify-center font-bold shadow-[0_8px_20px_-6px_rgba(99,102,241,0.7)] shrink-0 group-hover:scale-105 transition-transform duration-200">
            <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z"/>
            </svg>
        </div>

        @if($showText)
            <div class="flex flex-col">
                <span class="{{ $sizes['text'] }} font-extrabold tracking-tight text-[#000000] dark:text-[#fafafa] leading-none">
                    Nampung<span class="ny-gradient-text">Yuk</span>
                </span>
                @if($showTagline)
                    <span class="{{ $sizes['tagline'] }} font-medium tracking-wide text-[#666666] dark:text-[#a0a0a0] mt-0.5">
                        Developer Showcase
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
