@props(['name', 'value', 'label' => null, 'checked' => false])

@php
    // Mengecek apakah grup radio ini memiliki error validasi
    $hasError = $errors->has($name);
@endphp

<label
    class="{{ $hasError
        ? 'border-red-500 bg-red-50/30'
        : 'border-gray-200 bg-white has-[:checked]:border-equator-dark has-[:checked]:bg-equator-dark/5 has-[:checked]:ring-1 has-[:checked]:ring-equator-dark' }} group relative flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-all duration-200 hover:bg-gray-50">
    {{-- Wadah Input (Agar titik radio sejajar sempurna dengan teks) --}}
    <div class="mt-0.5 flex h-4 items-center">
        <input type="radio" name="{{ $name }}" value="{{ $value }}"
            {{ old($name, $checked) == $value ? 'checked' : '' }}
            class="peer h-4 w-4 border-gray-300 text-equator-dark transition-all focus:ring-2 focus:ring-equator-dark focus:ring-offset-1"
            {{ $attributes }}>
    </div>

    {{-- Area Teks --}}
    <div class="flex flex-col">
        @if ($label)
            <p class="text-sm font-bold leading-tight text-gray-900 transition-colors peer-checked:text-equator-dark">
                {{ $label }}
            </p>
        @endif

        {{-- Slot tambahan (Jika Anda ingin memberi deskripsi di bawah nama opsi) --}}
        @if ($slot->isNotEmpty())
            <p class="mt-1 text-xs font-medium leading-relaxed text-gray-500">
                {{ $slot }}
            </p>
        @endif
    </div>
</label>
