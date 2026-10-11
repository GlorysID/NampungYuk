@props([
    'size' => 'md', // 'sm', 'md', 'lg'
    'showText' => true,
    'showTagline' => false,
    'href' => route('projects.index'),
])

@php
    $sizes = [
        'sm' => [
            'icon' => 'w-8 h-8 rounded-lg',
            'glyph' => 'w-[18px] h-[18px]',
            'text' => 'text-[15px]',
            'tagline' => 'text-[9px]',
            'gap' => 'gap-2',
        ],
        'md' => [
            'icon' => 'w-9 h-9 rounded-lg',
            'glyph' => 'w-5 h-5',
            'text' => 'text-lg',
            'tagline' => 'text-[10px]',
            'gap' => 'gap-2.5',
        ],
        'lg' => [
            'icon' => 'w-12 h-12 rounded-xl',
            'glyph' => 'w-6 h-6',
            'text' => 'text-2xl',
            'tagline' => 'text-xs',
            'gap' => 'gap-3',
        ],
    ][$size] ?? [
        'icon' => 'w-9 h-9 rounded-lg',
        'glyph' => 'w-5 h-5',
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

        {{-- Solid contrast badge: black in light, white in dark (Vercel style) --}}
        <div class="{{ $sizes['icon'] }} flex items-center justify-center shrink-0 bg-[#18181b] dark:bg-[#fafafa] text-white dark:text-[#0a0a0a] group-hover:opacity-90 transition-opacity duration-200">
            <svg class="{{ $sizes['glyph'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8.5 8 5 12l3.5 4" />
                <path d="M15.5 8 19 12l-3.5 4" />
                <path d="M13.2 6.5 10.8 17.5" />
            </svg>
        </div>

        @if($showText)
            <div class="flex flex-col">
                <span class="{{ $sizes['text'] }} font-extrabold tracking-tight text-[#18181b] dark:text-[#fafafa] leading-none">
                    Nampung<span class="text-[#0070f3] dark:text-[#3291ff]">Yuk</span>
                </span>
                @if($showTagline)
                    <span class="{{ $sizes['tagline'] }} font-medium tracking-wide text-[#63636b] dark:text-[#a0a0a0] mt-0.5">
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
