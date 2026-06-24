{{--
    Language tab bar for admin forms.

    Toggles a shared Alpine `locale` variable that MUST be defined on an ancestor
    x-data scope; each translatable field is wrapped in `x-show="locale === '<code>'"`.
    One bar controls every translatable field on the form (across cards), so the
    editor switches the whole form's language at once. Loops config locales, so a
    third language adds a third tab automatically.
--}}
@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');
@endphp

@if (count($locales) > 1)
    <div class="mb-6 flex items-center gap-1 border-b border-gray-200">
        @foreach ($locales as $code => $meta)
            <button
                type="button"
                @click="locale = '{{ $code }}'"
                :class="locale === '{{ $code }}'
                    ? 'border-equator-bright text-equator-text'
                    : 'border-transparent text-gray-400 hover:text-gray-600'"
                class="-mb-px flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-bold transition-colors focus:outline-none"
            >
                <span>{{ strtoupper($code) }}</span>
                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                    {{ $code === $default ? 'bg-equator-bright/10 text-equator-bright' : 'bg-gray-100 text-gray-400' }}">
                    {{ $code === $default ? 'required' : 'optional' }}
                </span>
            </button>
        @endforeach
    </div>
@endif
