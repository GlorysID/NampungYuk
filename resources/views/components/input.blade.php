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
        <label for="{{ $inputId }}" class="flex items-center justify-between text-sm font-semibold text-[#0B0B0D] dark:text-[#EAE8E1]">
            <span class="flex items-center gap-1">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
                @endif
            </span>
            @if($hint)
                <span class="text-xs font-normal text-[#6E6D67] dark:text-[#8E8D86]">{{ $hint }}</span>
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
                   'class' => 'w-full px-4 py-2.5 text-sm sm:text-[15px] rounded-xl bg-[#EFECE6] dark:bg-[#121214] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder-[#6E6D67] dark:placeholder-[#8E8D86] border transition outline-none ' .
                   ($hasError
                       ? 'border-rose-400 dark:border-rose-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20'
                       : 'border-[#E2DFD7] dark:border-[#242426] focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#0B0B0D]/10 dark:focus:ring-[#EAE8E1]/10')
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
