@props(['name', 'label' => null, 'required' => false])

@php
    $hasError = $errors->has($name);
@endphp

<div class="space-y-1.5">

    {{-- LABEL --}}
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-bold tracking-wide text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    {{-- SELECT CONTAINER --}}
    <div class="relative">
        <select name="{{ $name }}" id="{{ $name }}"
            {{ $attributes->merge([
                'class' =>
                    'appearance-none block w-full rounded-xl border px-4 py-2.5 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-offset-1 cursor-pointer ' .
                    ($hasError
                        ? 'border-red-500 bg-red-50/30 focus:border-red-500 focus:ring-red-500/30'
                        : 'border-gray-200 bg-white focus:border-equator-dark focus:ring-equator-dark/20'),
            ]) }}>
            {{ $slot }}
        </select>

        {{-- CUSTOM CHEVRON (Elegan & Konsisten) --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </div>
    </div>

    {{-- ERROR HANDLING (Premium Format) --}}
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
