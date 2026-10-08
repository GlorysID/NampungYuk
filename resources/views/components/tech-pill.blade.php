@props([
    'name',
    'clickable' => true,
])

@if($clickable)
    <a href="{{ route('projects.index', ['tech' => $name]) }}"
       {{ $attributes->merge(['class' => 'tech-pill hover:-translate-y-0.5 active:translate-y-0']) }}
       title="Filter project dengan tech stack {{ $name }}">
        <span class="text-[#0070f3] dark:text-[#47a8ff] opacity-70">#</span>
        <span>{{ $name }}</span>
    </a>
@else
    <span {{ $attributes->merge(['class' => 'tech-pill']) }}>
        <span class="text-[#0070f3] dark:text-[#47a8ff] opacity-70">#</span>
        <span>{{ $name }}</span>
    </span>
@endif
