@props(['name', 'label' => null, 'checked' => false])

@php
    $hasError = $errors->has($name);
@endphp

<div class="space-y-1.5">
    <label
        class="{{ $hasError
            ? 'border-red-500 bg-red-50/30'
            : 'border-gray-200 bg-white has-[:checked]:border-equator-dark has-[:checked]:bg-equator-dark/5 has-[:checked]:ring-1 has-[:checked]:ring-equator-dark' }} group relative flex cursor-pointer items-center justify-between gap-4 rounded-xl border p-4 transition-all duration-200 hover:bg-gray-50">
        {{-- Area Teks Kiri --}}
        <div class="flex flex-col">
            @if ($label)
                <p
                    class="text-sm font-bold leading-tight text-gray-900 transition-colors group-has-[:checked]:text-equator-dark">
                    {{ $label }}
                </p>
            @endif

            @if ($slot->isNotEmpty())
                <p class="mt-1 text-xs font-medium leading-relaxed text-gray-500">
                    {{ $slot }}
                </p>
            @endif
        </div>

        {{-- TOGGLE SWITCH KANAN (Pure CSS) --}}
        <div class="relative inline-flex h-6 w-11 shrink-0 items-center">

            {{-- Trik Enterprise Laravel: Jika toggle mati, input hidden ini yang mengirim nilai '0' ke server --}}
            <input type="hidden" name="{{ $name }}" value="0">

            {{-- Input Checkbox Asli (Disembunyikan secara visual, tapi mengendalikan seluruh UI) --}}
            <input type="checkbox" name="{{ $name }}" value="1" {{ old($name, $checked) ? 'checked' : '' }}
                class="peer sr-only">

            {{-- Track (Jalur Latar Belakang) --}}
            <div
                class="h-6 w-11 rounded-full bg-gray-200 transition-colors duration-200 peer-checked:bg-equator-dark peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-equator-dark/50 peer-focus:ring-offset-2">
            </div>

            {{-- Thumb (Lingkaran Putih) --}}
            {{-- Catatan: Shadow kecil di sini mutlak diperlukan agar lingkaran putih terlihat terpisah dari latar abu-abu/biru --}}
            <div
                class="absolute left-1 top-1 h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5">
            </div>

        </div>
    </label>

    {{-- Error Handling Terintegrasi --}}
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
