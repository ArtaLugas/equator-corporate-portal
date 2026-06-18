<div class="space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Metric</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A statistic shown on the homepage stats strip (e.g. "200+ Projects Delivered").</p>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="value" label="Value"
                    :value="old('value', $metric->value ?? '')" placeholder="e.g. 200+" required />
                <x-admin.form.input name="label" label="Label"
                    :value="old('label', $metric->label ?? '')" placeholder="e.g. Projects Delivered" required />
            </div>

            @php
                $iconOptions = config('icons.lucide', []);
                $currentIcon = old('icon', $metric->icon ?? '');
            @endphp
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
                            <x-icon :name="$currentIcon ?: 'activity'" class="h-7 w-7 text-equator-text" />
                        </div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Preview</span>
                    </div>
                </div>

                <p class="mt-4 border-t border-gray-200 pt-4 text-xs text-gray-500">
                    Ikon diambil dari set kurasi (Lucide) agar bundle situs tetap ringan. Tampil di stats strip homepage.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="display_order" label="Display Order" type="number" min="0"
                    :value="old('display_order', $metric->display_order ?? 0)" placeholder="0" />
                <x-admin.form.select name="status" label="Status" required>
                    <option value="active" {{ old('status', $metric->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $metric->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </x-admin.form.select>
            </div>

            <x-admin.form.toggle name="is_featured" label="Featured"
                :checked="(bool) ($metric->is_featured ?? false)">
                Tandai metrik ini sebagai unggulan untuk ditonjolkan di homepage.
            </x-admin.form.toggle>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('icon');
            const wrapper = document.getElementById('icon-preview-wrapper');

            if (!input || !wrapper) {
                return;
            }

            const renderIcon = (iconName) => {
                wrapper.innerHTML =
                    `<i data-lucide="${iconName || 'activity'}" class="h-7 w-7 text-equator-text"></i>`;
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            renderIcon(input.value.trim());
            input.addEventListener('change', () => renderIcon(input.value.trim()));
        });
    </script>
@endpush
