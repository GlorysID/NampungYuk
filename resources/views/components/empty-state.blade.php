@props([
    'title' => 'Belum ada data',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="ny-card p-8 sm:p-12 text-center space-y-3">
    <div class="w-12 h-12 rounded-xl bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] mx-auto flex items-center justify-center border border-[#50d2c1]/30 dark:border-[#50d2c1]/30">
        {{ $icon ?? '' }}
        @if(!isset($icon))
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        @endif
    </div>

    <div class="space-y-1 max-w-sm mx-auto">
        <h3 class="font-bold text-sm sm:text-base text-[#10161f] dark:text-[#eaecf0]">
            {{ $title }}
        </h3>
        @if($description)
            <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a] leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    @if($actionLabel && $actionUrl)
        <div class="pt-2">
            <x-button :href="$actionUrl" variant="primary" size="sm">
                {{ $actionLabel }}
            </x-button>
        </div>
    @endif
</div>
