@props([
    'name',
    'clickable' => true,
])

@if($clickable)
    @auth
        <a href="{{ route('projects.index', ['tech' => $name]) }}"
           {{ $attributes->merge(['class' => 'tech-pill hover:-translate-y-0.5 active:translate-y-0']) }}
           title="Filter project dengan tech stack {{ $name }}">
            <span class="text-[#0070f3] dark:text-[#47a8ff] opacity-70">#</span>
            <span>{{ $name }}</span>
        </a>
    @else
        <button type="button"
                @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                {{ $attributes->merge(['class' => 'tech-pill hover:-translate-y-0.5 active:translate-y-0 cursor-pointer']) }}
                title="Masuk untuk memfilter project dengan tech stack {{ $name }}">
            <span class="text-[#0070f3] dark:text-[#47a8ff] opacity-70">#</span>
            <span>{{ $name }}</span>
        </button>
    @endauth
@else
    <span {{ $attributes->merge(['class' => 'tech-pill']) }}>
        <span class="text-[#0070f3] dark:text-[#47a8ff] opacity-70">#</span>
        <span>{{ $name }}</span>
    </span>
@endif
