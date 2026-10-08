@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">

        {{-- Results counter --}}
        <p class="text-sm text-[#666666] dark:text-[#a0a0a0] order-2 sm:order-1">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-[#171717] dark:text-[#fafafa]">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-[#171717] dark:text-[#fafafa]">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium text-[#171717] dark:text-[#fafafa]">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        {{-- Page controls --}}
        <div class="inline-flex items-center gap-1 order-1 sm:order-2">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-[#e2e2e2] dark:border-[#1f1f1f] text-[#8f8f8f] dark:text-[#666666] cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   aria-label="{{ __('pagination.previous') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-[#e2e2e2] dark:border-[#1f1f1f] text-[#666666] dark:text-[#a0a0a0] hover:bg-[#f2f2f2] dark:hover:bg-[#171717] hover:text-[#171717] dark:hover:text-[#fafafa] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 text-sm text-[#8f8f8f] dark:text-[#666666]">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-md text-sm font-semibold bg-[#000000] text-[#ffffff] dark:bg-[#fafafa] dark:text-[#000000]">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                               class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-md text-sm font-medium text-[#666666] dark:text-[#a0a0a0] border border-transparent hover:bg-[#f2f2f2] dark:hover:bg-[#171717] hover:text-[#171717] dark:hover:text-[#fafafa] transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   aria-label="{{ __('pagination.next') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-[#e2e2e2] dark:border-[#1f1f1f] text-[#666666] dark:text-[#a0a0a0] hover:bg-[#f2f2f2] dark:hover:bg-[#171717] hover:text-[#171717] dark:hover:text-[#fafafa] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-[#e2e2e2] dark:border-[#1f1f1f] text-[#8f8f8f] dark:text-[#666666] cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
