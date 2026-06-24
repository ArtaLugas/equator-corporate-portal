{{--
    Translation-status indicator for CMS list rows.

    Shows the default locale as the source (always ✓) and, for every other
    locale, an EXPLICIT completeness badge: "3/5" fields translated, colour-coded
    (green = complete, amber = partial, grey = none) with the percentage in the
    tooltip — so admins see exactly how much of each record is translated without
    opening it. Driven by the model's translationStat().

    Usage:  <x-admin.translation-status :model="$service" />
--}}
@props(['model'])

@php
    $default = config('locales.default');
    $others = array_values(array_diff(array_keys(config('locales.supported', [])), [$default]));
@endphp

<div class="flex flex-wrap items-center gap-1">

    {{-- Default locale: the source content --}}
    <span
        class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700"
        title="{{ strtoupper($default) }} — source language"
    >
        {{ strtoupper($default) }}
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
        </svg>
    </span>

    {{-- Other locales: explicit "translated/source" completeness badge --}}
    @foreach ($others as $locale)
        @php
            $stat = $model->translationStat($locale);
            $classes = $stat['percent'] === 100
                ? 'bg-emerald-50 text-emerald-700'
                : ($stat['percent'] === 0 ? 'bg-gray-100 text-gray-400' : 'bg-amber-50 text-amber-700');
        @endphp

        <span
            class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-bold {{ $classes }}"
            title="{{ strtoupper($locale) }} translation — {{ $stat['translated'] }} of {{ $stat['source'] }} fields ({{ $stat['percent'] }}%)"
        >
            {{ strtoupper($locale) }}

            @if ($stat['source'] === 0)
                {{-- Nothing in the source to translate yet --}}
                <span>—</span>
            @else
                <span>{{ $stat['translated'] }}/{{ $stat['source'] }}</span>

                @if ($stat['percent'] === 100)
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                @endif
            @endif
        </span>
    @endforeach

</div>
