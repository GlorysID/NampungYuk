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
        <label for="{{ $inputId }}" class="flex items-center justify-between text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">
            <span class="flex items-center gap-1">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
                @endif
            </span>
            @if($hint)
                <span class="text-[11px] font-normal text-[#63636b] dark:text-[#a0a0a0]">{{ $hint }}</span>
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
                   'class' => 'w-full px-4 py-2.5 text-sm rounded-[14px] bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] placeholder-[#888888] dark:placeholder-[#6b7396] border transition ' .
                   ($hasError
                       ? 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/15'
                       : 'border-[#e4e4e7] dark:border-[#1f1f1f] focus:border-[#0070f3] dark:focus:border-[#3291ff] focus:ring-4 focus:ring-[#3291ff]/15')
               ]) }} />
    </div>

    @if($hasError)
        <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1" role="alert">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @endif
</div>
