@props(['name', 'label' => null, 'required' => false, 'accept' => null, 'helpText' => null, 'currentFile' => null])

<div class="space-y-2">

    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-bold tracking-wide text-gray-700">

            {{ $label }}

            @if ($required)
                <span class="text-red-500">*</span>
            @endif

        </label>
    @endif

    @if ($currentFile)
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">

            <p class="mb-2 text-xs font-semibold text-gray-500">
                Current File
            </p>

            <a href="{{ asset('storage/' . $currentFile) }}" target="_blank"
                class="text-sm font-medium text-equator-dark hover:underline">

                View Current Document

            </a>

        </div>
    @endif

    <input type="file" id="{{ $name }}" name="{{ $name }}" accept="{{ $accept }}"
        @class([
            'block w-full rounded-xl border px-4 py-3 text-sm shadow-sm transition-all',
            'border-red-500' => $errors->has($name),
            'border-gray-200' => !$errors->has($name),
        ])>

    @if ($helpText)
        <p class="text-xs text-gray-500">
            {{ $helpText }}
        </p>
    @endif

    @error($name)
        <p class="text-xs font-semibold text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>
