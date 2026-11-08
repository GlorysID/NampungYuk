@props([
    'name',
    'clickable' => true,
])

@php
    $classes = "inline-flex items-center gap-1 font-mono text-[12px] px-2.5 py-1 rounded-lg bg-[#EFECE6] dark:bg-[#161619] text-[#4A4944] dark:text-[#A8A7A0] border border-[#E2DFD7] dark:border-[#242426] hover:border-[#D0CDC3] dark:hover:border-[#333336] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition-colors select-none";
@endphp

@if($clickable)
    <a href="{{ route('projects.index', ['tech' => $name]) }}"
       @click.stop
       {{ $attributes->merge(['class' => $classes]) }}
       title="Filter project dengan tech stack {{ $name }}">
        <span class="text-emerald-600 dark:text-emerald-400 opacity-80">#</span>
        <span>{{ $name }}</span>
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        <span class="text-emerald-600 dark:text-emerald-400 opacity-80">#</span>
        <span>{{ $name }}</span>
    </span>
@endif
