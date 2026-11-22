@props([
    'name',
    'id' => null,
    'options' => [],
    'placeholder' => 'Pilih...',
    'size' => 'md',
    'align' => 'left',
    'minWidth' => 'min-w-[190px]',
])

@php
    $controlId = $id ?? $name;
    $triggerSize = $size === 'sm'
        ? 'h-8 px-2.5 text-[11.5px] rounded-lg'
        : 'h-9 px-3 text-xs rounded-xl';
    $normalizedOptions = array_values(array_map(static function ($opt): array {
        return [
            'value' => (string) ($opt['value'] ?? ''),
            'label' => (string) ($opt['label'] ?? ''),
            'hint' => isset($opt['hint']) ? (string) $opt['hint'] : '',
        ];
    }, $options));
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-block']) }}
     x-data="{
        open: false,
        selected: '',
        options: @js($normalizedOptions),
        get currentOption() {
            return this.options.find(o => String(o.value) === String(this.selected)) || null;
        },
        select(val) {
            this.selected = String(val);
            this.open = false;
        }
     }"
     x-modelable="selected"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    <input type="hidden" name="{{ $name }}" :value="selected">

    <button type="button"
            id="{{ $controlId }}"
            @click="open = !open"
            aria-haspopup="listbox"
            :aria-expanded="open"
            class="inline-flex items-center justify-between gap-2 font-medium bg-[var(--surface-muted)] hover:bg-[var(--surface-hover)] border border-[var(--border-color)] hover:border-[var(--border-strong)] text-[var(--primary)] transition cursor-pointer outline-none focus:border-[var(--primary)] {{ $triggerSize }}">
        <span class="truncate"
              :class="currentOption ? 'text-[var(--primary)]' : 'text-[var(--text-muted)]'"
              x-text="currentOption ? currentOption.label : @js($placeholder)"></span>
        <svg class="w-3.5 h-3.5 shrink-0 text-[var(--text-muted)] transition-transform duration-150"
             :class="open ? 'rotate-180 text-[var(--primary)]' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-120"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-[0.98]"
         role="listbox"
         class="absolute z-40 mt-1.5 {{ $align === 'right' ? 'right-0' : 'left-0' }} {{ $minWidth }} max-h-64 overflow-y-auto rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-2xl p-1">
        <template x-for="opt in options" :key="opt.value">
            <button type="button"
                    role="option"
                    :aria-selected="String(opt.value) === String(selected)"
                    @click="select(opt.value)"
                    :class="String(opt.value) === String(selected)
                        ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold'
                        : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)]/60 font-medium'"
                    class="w-full flex items-center justify-between gap-2.5 px-2.5 py-2 rounded-lg text-xs text-left transition cursor-pointer">
                <div class="min-w-0 flex-1">
                    <span class="block truncate" x-text="opt.label"></span>
                    <span x-show="opt.hint" class="block text-[10.5px] font-normal text-[var(--text-faint)] mt-0.5 leading-snug" x-text="opt.hint"></span>
                </div>
                <svg x-show="String(opt.value) === String(selected)"
                     class="w-3.5 h-3.5 shrink-0 text-emerald-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </template>
    </div>
</div>
