@props([
    'name',
    'clickable' => true,
    'variant' => 'pill',
])

@php
    $classes = $variant === 'compact'
        ? 'inline-flex items-center font-mono text-[11px] px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)] hover:border-[var(--border-strong)] transition-colors select-none'
        : 'inline-flex items-center gap-1 font-mono text-[12px] px-2.5 py-1 rounded-lg bg-[var(--surface-muted)] text-[var(--text-soft)] hover:text-[var(--primary)] transition-colors select-none';
@endphp

@if($clickable)
    <a href="{{ route('projects.index', ['tech' => $name]) }}"
       @click.stop
       {{ $attributes->merge(['class' => $classes]) }}
       title="Filter project dengan tech stack {{ $name }}">
        @if($variant !== 'compact')
            <span class="text-emerald-500/80">#</span>
        @endif
        <span>{{ $name }}</span>
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        @if($variant !== 'compact')
            <span class="text-emerald-500/80">#</span>
        @endif
        <span>{{ $name }}</span>
    </span>
@endif
