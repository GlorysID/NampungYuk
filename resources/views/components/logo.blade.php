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

        <!-- Gradient icon badge — "wadah/nampung" metaphor: box with a drop-in arrow -->
        <div class="{{ $sizes['icon'] }} ny-gradient-bg text-white flex items-center justify-center font-bold shadow-[0_8px_20px_-6px_rgba(0,0,0,0.4)] shrink-0 group-hover:scale-105 transition-transform duration-200">
            <svg class="w-[60%] h-[60%]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                {{-- open container / tray --}}
                <path d="M3 10.5 4.6 5.4A1.5 1.5 0 0 1 6 4.35h12a1.5 1.5 0 0 1 1.4 1.05L21 10.5" />
                {{-- tray body --}}
                <path d="M3 10.5h4.5l1.2 2.1h6.6l1.2-2.1H21v6.9a1.6 1.6 0 0 1-1.6 1.6H4.6A1.6 1.6 0 0 1 3 17.4z" />
                {{-- drop-in arrow (something being "nampung"-ed) --}}
                <path d="M12 1.8v4.2" />
                <path d="M10.4 4.4 12 6l1.6-1.6" />
            </svg>
        </div>

        @if($showText)
            <div class="flex flex-col">
                <span class="{{ $sizes['text'] }} font-extrabold tracking-tight text-[#18181b] dark:text-[#fafafa] leading-none">
                    Nampung<span class="ny-gradient-text">Yuk</span>
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
