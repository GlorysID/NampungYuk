@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || ($errors && $errors->has($name));
    $errorMessage = $error ?? ($errors ? $errors->first($name) : null);
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="flex items-center justify-between text-sm font-semibold text-[var(--primary)]">
            <span class="flex items-center gap-1">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
                @endif
            </span>
            @if($hint)
                <span class="text-xs font-normal text-[var(--text-muted)]">{{ $hint }}</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input type="{{ $type }}"
               name="{{ $name }}"
               id="{{ $inputId }}"
               value="{{ old($name, $value) }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               {{ $attributes->merge([
                   'class' => 'w-full px-4 py-2.5 text-sm sm:text-[15px] rounded-xl bg-[var(--surface-muted-flat)] text-[var(--primary)] placeholder-[var(--text-muted)] border transition outline-none ' .
                   ($hasError
                       ? 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20'
                       : 'border-[var(--border-color)] focus:border-[var(--primary)] focus:ring-1 focus:ring-[var(--primary)]/10 ')
               ]) }} />
    </div>

    @if($hasError)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1" role="alert">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @endif
</div>
