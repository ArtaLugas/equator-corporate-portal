@props(['name', 'label' => null, 'value' => null, 'placeholder' => '', 'rows' => 4, 'required' => false])

@php

    $hasError = $errors->has($name);

@endphp

<div class="space-y-1.5">

    {{-- LABEL --}}
    @if ($label)

        <label for="{{ $name }}" class="block text-xs font-bold tracking-wide text-gray-700">

            {{ $label }}

            @if ($required)
                <span class="text-red-500">*</span>
            @endif

        </label>

    @endif

    {{-- TEXTAREA --}}
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        @class([
            'block w-full rounded-xl border px-4 py-3 text-sm text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:bg-gray-50 resize-none',

            'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $hasError,

            'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$hasError,
        ])>{{ old($name, $value) }}</textarea>

    {{-- ERROR --}}
    @error($name)
        <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">

            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="mt-0.5 shrink-0">

                <circle cx="12" cy="12" r="10" />
                <line x1="12" x2="12" y1="8" y2="12" />
                <line x1="12" x2="12" y1="16" y2="16" />

            </svg>

            {{ $message }}

        </p>
    @enderror

</div>
