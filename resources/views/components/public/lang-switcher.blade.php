{{--
    Public language switcher (desktop dropdown). Preserves the current page when
    switching: locale_url() rebuilds the current route in the target locale.
    Self-contained (inline SVG) so it has no icon-component dependency.
--}}
@php
    $current = app()->getLocale();
    $locales = config('locales.supported', []);
@endphp

@if (count($locales) > 1)
    <div x-data="{ open: false }" class="relative">
        <button
            type="button"
            @click="open = !open"
            @keydown.escape.window="open = false"
            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-2 text-sm font-bold text-gray-600 transition-colors hover:text-equator-dark"
            aria-haspopup="true"
            :aria-expanded="open.toString()"
            aria-label="{{ __('nav.language') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" /><path d="M2 12h20" />
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
            </svg>
            <span>{{ strtoupper($current) }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                class="transition-transform" :class="open && 'rotate-180'">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.origin.top.right
            @click.outside="open = false"
            class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-gray-100 bg-white py-1 shadow-lg"
            role="menu"
        >
            @foreach ($locales as $code => $meta)
                <a
                    href="{{ locale_url($code) }}"
                    hreflang="{{ $code }}"
                    @if ($code === $current) aria-current="true" @endif
                    class="flex items-center justify-between px-4 py-2.5 text-sm transition-colors
                        {{ $code === $current
                            ? 'font-bold text-equator-dark'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-equator-dark' }}"
                    role="menuitem"
                >
                    <span>{{ $meta['native'] }}</span>
                    @if ($code === $current)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
