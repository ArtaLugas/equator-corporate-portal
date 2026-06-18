@props(['name', 'label' => null, 'checked' => false])

@php
    $hasError = $errors->has($name);
@endphp

<div class="space-y-1.5">
    <label
        class="{{ $hasError
            ? 'border-red-500 bg-red-50/30'
            : 'border-gray-200 bg-white has-[:checked]:border-equator-dark has-[:checked]:bg-equator-dark/5 has-[:checked]:ring-1 has-[:checked]:ring-equator-dark' }} group relative flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-all duration-200 hover:bg-gray-50">
        {{-- Wadah Input agar sejajar presisi dengan baris pertama teks --}}
        <div class="mt-0.5 flex h-4 items-center">
            <input type="checkbox" name="{{ $name }}" value="1" {{ old($name, $checked) ? 'checked' : '' }}
                class="peer h-4 w-4 rounded-[4px] border-gray-300 text-equator-dark transition-all focus:ring-2 focus:ring-equator-dark focus:ring-offset-1"
                {{ $attributes }}>
        </div>

        {{-- Area Teks --}}
        <div class="flex flex-col">
            @if ($label)
                <p
                    class="text-sm font-bold leading-tight text-gray-900 transition-colors peer-checked:text-equator-dark">
                    {{ $label }}
                </p>
            @endif

            @if ($slot->isNotEmpty())
                <p class="mt-1 text-xs font-medium leading-relaxed text-gray-500">
                    {{ $slot }}
                </p>
            @endif
        </div>
    </label>

    {{-- Error Handling bawaan komponen agar tidak perlu ditulis manual di luar --}}
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
