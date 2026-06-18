<div class="space-y-8">

    {{-- GENERAL --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold text-equator-text">
                Core Value Information
            </h2>
        </div>

        <div class="space-y-6">

            <x-admin.form.input name="title" label="Title" :value="old('title', $coreValue->title ?? '')" required />

            @php
                $iconOptions = config('icons.lucide', []);
                $currentIcon = old('icon', $coreValue->icon ?? '');
            @endphp
            <div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">

                        {{-- DROPDOWN --}}
                        <div class="flex-1">
                            <x-admin.form.select name="icon" label="Icon">
                                <option value="">— No icon —</option>
                                @foreach ($iconOptions as $val => $label)
                                    <option value="{{ $val }}" @selected($currentIcon === $val)>{{ $label }}</option>
                                @endforeach
                                @if ($currentIcon && !array_key_exists($currentIcon, $iconOptions))
                                    <option value="{{ $currentIcon }}" selected>{{ $currentIcon }} (custom)</option>
                                @endif
                            </x-admin.form.select>
                        </div>

                        {{-- PREVIEW --}}
                        <div class="flex flex-col items-center gap-2">
                            <div id="icon-preview-wrapper"
                                class="flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <x-icon :name="$currentIcon ?: 'shield'" class="h-7 w-7 text-equator-text" />
                            </div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Preview</span>
                        </div>
                    </div>

                    <p class="mt-4 border-t border-gray-200 pt-4 text-xs text-gray-500">
                        Ikon diambil dari set kurasi (Lucide) agar bundle situs tetap ringan.
                    </p>
                </div>
            </div>

            <x-admin.form.wysiwyg name="description" label="Description" :value="old('description', $coreValue->description ?? '')" />

        </div>

    </div>

    {{-- SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold text-equator-text">
                Visibility Settings
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">

                <option value="inactive" {{ old('status', $coreValue->status ?? '') == 'inactive' ? 'selected' : '' }}>

                    Inactive

                </option>

                <option value="active" {{ old('status', $coreValue->status ?? '') == 'active' ? 'selected' : '' }}>

                    Active

                </option>

            </x-admin.form.select>

            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $coreValue->display_order ?? 1)" />

        </div>

    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const input =
                document.getElementById('icon');

            const wrapper =
                document.getElementById(
                    'icon-preview-wrapper'
                );

            if (!input || !wrapper) {
                return;
            }

            function renderIcon(iconName) {

                wrapper.innerHTML = `
            <i
                data-lucide="${iconName || 'shield'}"
                class="h-7 w-7 text-equator-text">
            </i>
        `;

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            renderIcon(
                input.value.trim()
            );

            input.addEventListener('change', () => {

                renderIcon(
                    input.value.trim()
                );

            });

        });
    </script>
@endpush
