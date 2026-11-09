@props([
    'name',
    'clickable' => true,
])

@php
    $classes = "inline-flex items-center gap-1 font-mono text-[12px] px-2.5 py-1 rounded-lg bg-[var(--surface-muted)] text-[var(--text-soft)] hover:text-[var(--primary)] transition-colors select-none";
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
