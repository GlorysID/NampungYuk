@props([
    'name',
    'clickable' => true,
])

@if($clickable)
    <a href="{{ route('projects.index', ['tech' => $name]) }}" 
       {{ $attributes->merge(['class' => 'tech-pill hover:scale-[1.02] active:scale-[0.98]']) }}
       title="Filter project dengan tech stack {{ $name }}">
        <span class="text-teal-600 dark:text-teal-400 opacity-70">#</span>
        <span>{{ $name }}</span>
    </a>
@else
    <span {{ $attributes->merge(['class' => 'tech-pill']) }}>
        <span class="text-teal-600 dark:text-teal-400 opacity-70">#</span>
        <span>{{ $name }}</span>
    </span>
@endif
