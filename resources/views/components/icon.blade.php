@props(['name' => null, 'class' => 'h-5 w-5'])

@php
    // Fallback sisi-server: kalau nama tak ada di SELURUH ikon ter-bundle
    // (bukan cuma subset dropdown CMS), pakai ikon fallback → data-lucide
    // DIJAMIN valid, tak akan memicu error JS. Ikon UI (cms:false) tetap lolos.
    $allowed = config('icons.all', config('icons.lucide', []));
    $fallback = config('icons.fallback', 'circle-help');
    $icon = $name && array_key_exists($name, $allowed) ? $name : $fallback;
@endphp

<i data-lucide="{{ $icon }}" aria-hidden="true" {{ $attributes->merge(['class' => $class]) }}></i>
