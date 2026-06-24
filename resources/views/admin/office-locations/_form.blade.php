@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['name', 'address'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div class="space-y-6" x-data="{ locale: @js($activeTab) }">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Office Location</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A contact point (head office or branch) shown on the public
                Contact page & footer.</p>
        </div>

        <div class="space-y-6">

            {{-- ALL-OR-NOTHING TRANSLATION SUMMARY --}}
            @if ($translationSummaries->isNotEmpty())
                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mt-0.5 shrink-0 text-amber-500">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <line x1="12" x2="12" y1="9" y2="13" />
                        <line x1="12" x2="12.01" y1="17" y2="17" />
                    </svg>
                    <div class="space-y-1">
                        @foreach ($translationSummaries as $l)
                            <p class="text-sm font-semibold text-amber-800">{{ $errors->first("translation_{$l}") }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- LANGUAGE TABS (control name + address) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input
                        name="name_{{ $code }}"
                        label="Location Name ({{ strtoupper($code) }})"
                        value="{{ old('name_' . $code, $location->{'name_' . $code} ?? '') }}"
                        placeholder="e.g. Head Office — Jakarta"
                        :required="$code === $default" />

                    <x-admin.form.textarea
                        name="address_{{ $code }}"
                        label="Address ({{ strtoupper($code) }})"
                        rows="3"
                        placeholder="Full street address"
                        :value="old('address_' . $code, $location->{'address_' . $code} ?? '')" />

                </div>
            @endforeach

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="phone" label="Phone"
                    :value="old('phone', $location->phone ?? '')" placeholder="+62 21 1234 5678" />
                <x-admin.form.input type="email" name="email" label="Email"
                    :value="old('email', $location->email ?? '')" placeholder="office@company.com" />
            </div>

            <div>
                <x-admin.form.textarea name="map_embed" label="Google Maps Embed (iframe)" rows="3"
                    placeholder="Paste the full Google Maps <iframe> embed code here"
                    :value="$location->map_embed ?? ''" />
                <p class="mt-1.5 text-xs font-medium text-gray-400">Google Maps → Share → Embed a map → copy the full
                    <code>&lt;iframe&gt;</code> code.</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Visibility Settings</h2>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input type="number" name="display_order" label="Display Order" min="0"
                    :value="old('display_order', $location->display_order ?? 1)" placeholder="Lower numbers appear first" />
                <x-admin.form.select name="status" label="Status" required>
                    <option value="active" {{ old('status', $location->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $location->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </x-admin.form.select>
            </div>

            <x-admin.form.toggle name="is_primary" label="Primary Location"
                :checked="(bool) old('is_primary', $location->is_primary ?? false)">
                Lokasi utama — ditampilkan di footer & dipakai sebagai kontak utama. Hanya boleh satu; memilih ini
                otomatis menonaktifkan primary pada lokasi lain.
            </x-admin.form.toggle>
        </div>
    </div>
</div>
