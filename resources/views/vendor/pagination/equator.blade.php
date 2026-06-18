@if ($paginator->hasPages())
    @php
        // Ikon panah (SVG) agar tidak bergantung pada font-icon eksternal.
        $prevIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
        $nextIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';

        $baseItem = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold transition-colors duration-200';
        $idle = 'border border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-equator-dark';
        $active = 'border border-equator-dark bg-equator-dark text-white shadow-sm';
        $disabled = 'cursor-not-allowed border border-gray-100 bg-gray-50 text-gray-300';
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="flex flex-col items-center justify-between gap-4 sm:flex-row">

        {{-- Result summary --}}
        <p class="text-xs font-medium text-gray-500">
            Showing
            <span class="font-bold text-equator-dark">{{ $paginator->firstItem() ?? 0 }}</span>
            –
            <span class="font-bold text-equator-dark">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-bold text-equator-dark">{{ $paginator->total() }}</span>
            results
        </p>

        {{-- Pager --}}
        <div class="flex items-center gap-1.5">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $baseItem }} {{ $disabled }}" aria-disabled="true">{!! $prevIcon !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}"
                    class="{{ $baseItem }} {{ $idle }}">{!! $prevIcon !!}</a>
            @endif

            {{-- Numbers (disembunyikan di mobile agar tidak berdesakan) --}}
            <div class="hidden items-center gap-1.5 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $baseItem }} cursor-default border-0 bg-transparent text-gray-400">…</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $baseItem }} {{ $active }}" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $baseItem }} {{ $idle }}"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Indikator ringkas khusus mobile --}}
            <span class="{{ $baseItem }} border-0 bg-transparent text-xs text-gray-500 sm:hidden">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}"
                    class="{{ $baseItem }} {{ $idle }}">{!! $nextIcon !!}</a>
            @else
                <span class="{{ $baseItem }} {{ $disabled }}" aria-disabled="true">{!! $nextIcon !!}</span>
            @endif

        </div>
    </nav>
@endif
