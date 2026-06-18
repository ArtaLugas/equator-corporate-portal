@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'optional' => false,
    'textarea' => false,
    'hint' => null,
    'value' => null,
])

@php
    $id = 'field_' . $name;
    $hasError = $errors->has($name);
    $base =
        'block w-full border bg-white px-4 py-3 text-sm text-slate-900 transition-all duration-300 placeholder:text-slate-400 focus:border-equator-dark focus:outline-none focus:ring-1 focus:ring-equator-dark/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
    $border = $hasError ? 'border-red-500' : 'border-slate-200';

    // aria-describedby menautkan hint dan/atau error ke input untuk screen reader.
    $describedBy = collect([$hint ? $id . '_hint' : null, $hasError ? $id . '_error' : null])
        ->filter()
        ->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-600">
        {{ $label }}
        @if ($required)
            <span class="text-equator-orange" aria-hidden="true">*</span>
        @elseif ($optional)
            <span class="font-medium normal-case tracking-normal text-slate-500">(optional)</span>
        @endif
    </label>

    @if ($textarea)
        <textarea id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([$base, $border, 'resize-none']) }}>{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ old($name, $value) }}"
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->class([$base, $border]) }}>
    @endif

    @if ($hint && ! $hasError)
        <p id="{{ $id }}_hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}_error" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
