@props(['name', 'label' => null, 'value' => ''])

<div class="space-y-2">

    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-bold tracking-wide text-gray-700">
            {{ $label }}
        </label>
    @endif

    <div x-data x-init="setTimeout(() => {
        if (window.initCkEditor) {
            window.initCkEditor($refs.editor)
        }
    }, 100)">

        <textarea x-ref="editor" id="{{ $name }}" name="{{ $name }}">{{ old($name, $value) }}</textarea>

    </div>

    @error($name)
        <p class="text-xs font-semibold text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>
