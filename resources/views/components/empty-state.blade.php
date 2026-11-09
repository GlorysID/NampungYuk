@props([
    'title' => 'Belum ada data',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-10 sm:p-14 text-center space-y-4 shadow-xs">
    <div class="w-14 h-14 rounded-2xl bg-[var(--surface-muted)] text-[var(--primary)] mx-auto flex items-center justify-center border border-[var(--border-color)]">
        {{ $icon ?? '' }}
        @if(!isset($icon))
            <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        @endif
    </div>

    <div class="space-y-1.5 max-w-sm mx-auto">
        <h3 class="font-bold text-base text-[var(--primary)]">
            {{ $title }}
        </h3>
        @if($description)
            <p class="text-sm text-[var(--text-muted)] leading-relaxed">
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
