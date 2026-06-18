@props([
    'label' => false,
    'name',
    'type' => 'text',
    'required' => false,
])

@php
    // Deteksi otomatis apakah input ini memiliki error validasi
    $hasError = $errors->has($name);

    // Base classes yang mengadaptasi gaya Shadcn UI
    $baseClasses = 'flex h-11 w-full rounded-xl border bg-white px-4 py-2 text-sm text-equator-text shadow-sm transition-all duration-200 file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-50';

    // Dynamic state classes berdasarkan status error
    $stateClasses = $hasError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/30'
        : 'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20';
@endphp

<div class="space-y-1.5">
    {{-- Label Logic --}}
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-bold text-gray-700 tracking-wide">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Input Element --}}
    <div class="relative">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            {{-- Menggabungkan class bawaan dengan atribut tambahan seperti placeholder, readonly, dll --}}
            {{ $attributes->merge(['class' => $baseClasses . ' ' . $stateClasses]) }}
        >

        {{-- Error Icon Alert (Opsional: Memberikan indikator visual tambahan di dalam input) --}}
        @if($hasError)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12" y1="16" y2="16"/></svg>
            </div>
        @endif
    </div>

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs font-semibold text-red-600 mt-1 flex items-start gap-1">
            {{ $message }}
        </p>
    @enderror
</div>
