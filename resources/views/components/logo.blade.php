@props([
    'size' => 'md', // 'sm', 'md', 'lg'
    'showText' => true,
    'showTagline' => false,
    'variant' => 'mark', // 'mark' (icon + text) or 'full' (full logo graphic image)
    'href' => route('projects.index'),
])

@php
    $sizes = [
        'sm' => [
            'icon' => 'w-8 h-8',
            'full' => 'h-7 w-auto',
            'text' => 'text-[15px]',
            'tagline' => 'text-[9px]',
            'gap' => 'gap-2',
        ],
        'md' => [
            'icon' => 'w-9 h-9',
            'full' => 'h-9 w-auto',
            'text' => 'text-lg',
            'tagline' => 'text-[10px]',
            'gap' => 'gap-2.5',
        ],
        'lg' => [
            'icon' => 'w-12 h-12',
            'full' => 'h-12 w-auto',
            'text' => 'text-2xl',
            'tagline' => 'text-xs',
            'gap' => 'gap-3',
        ],
    ][$size] ?? [
        'icon' => 'w-9 h-9',
        'full' => 'h-9 w-auto',
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

        @if($variant === 'full')
            {{-- FULL GRAPHIC IMAGE LOGO (TRANSPARENT) --}}
            <img src="{{ asset('images/lightmodelogo.png') }}" 
                 alt="NampungYuk" 
                 class="{{ $sizes['full'] }} object-contain block dark:hidden group-hover:opacity-90 transition-opacity">
            <img src="{{ asset('images/darkmodelogo.png') }}" 
                 alt="NampungYuk" 
                 class="{{ $sizes['full'] }} object-contain hidden dark:block group-hover:opacity-90 transition-opacity">
        @else
            {{-- BRAND MARK BADGE (TRANSPARENT PNG) --}}
            <div class="{{ $sizes['icon'] }} relative shrink-0 group-hover:scale-105 transition-transform duration-200">
                <img src="{{ asset('images/lightmode-icon.png') }}" 
                     alt="NampungYuk Logo" 
                     class="w-full h-full object-contain block dark:hidden drop-shadow-xs">
                <img src="{{ asset('images/darkmode-icon.png') }}" 
                     alt="NampungYuk Logo" 
                     class="w-full h-full object-contain hidden dark:block drop-shadow-xs">
            </div>

            @if($showText)
                <div class="flex flex-col">
                    <span class="{{ $sizes['text'] }} font-extrabold tracking-tight text-[#18181b] dark:text-[#fafafa] leading-none">
                        Nampung<span class="text-[#0070f3] dark:text-[#00c2cb]">Yuk</span>
                    </span>
                    @if($showTagline)
                        <span class="{{ $sizes['tagline'] }} font-medium tracking-wide text-[#63636b] dark:text-[#a0a0a0] mt-0.5">
                            Developer Showcase
                        </span>
                    @endif
                </div>
            @endif
        @endif

    @if($href)
        </a>
    @else
        </div>
    @endif
</div>
