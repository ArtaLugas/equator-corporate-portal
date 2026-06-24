@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');
    $editing = isset($credential) && $credential->exists;
    $autoSlug = ! $editing || config('cms.auto_regenerate_slug', true);

    // Open the tab of the first locale that has a validation error.
    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'issuer', 'description'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));

    $existingItems = old('items', $editing
        ? $credential->items->map(fn ($it) => [
            'id' => $it->id,
            'title_en' => $it->title_en,
            'title_id' => $it->title_id,
            'description_en' => $it->description_en,
            'description_id' => $it->description_id,
        ])->values()->all()
        : []);
@endphp

<div x-data="{
    locale: @js($activeTab),
    autoSlug: @js($autoSlug),
    titleEn: @js(old('title_' . $default, $credential->{'title_' . $default} ?? '')),
    slug: @js(old('slug', $credential->slug ?? '')),
    items: @js($existingItems),
    deletedItems: [],
    generateSlug() {
        if (! this.autoSlug) return;
        this.slug = this.titleEn.toString().toLowerCase().trim()
            .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
    },
    addItem() {
        this.items.push({ id: '', title_en: '', title_id: '', description_en: '', description_id: '' });
    },
    removeItem(i) {
        if (this.items[i].id) this.deletedItems.push(this.items[i].id);
        this.items.splice(i, 1);
    },
}" x-effect="generateSlug()" class="space-y-6">

    {{-- ===================================================== --}}
    {{-- CARD 1 : CREDENTIAL --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Credential</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Core details of the credential. Title, issuer and
                description are per-language; everything else is shared.</p>
        </div>

        <div class="space-y-6">

            {{-- CATEGORY (not translated; string-backed, config-driven) --}}
            <x-admin.form.select name="category" label="Category" required>
                <option value="">Select Category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}"
                        {{ old('category', $credential->category ?? '') === $cat ? 'selected' : '' }}>
                        {{ __('credentials.categories.' . $cat) }}
                    </option>
                @endforeach
            </x-admin.form.select>

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

            {{-- LANGUAGE TABS --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input name="title_{{ $code }}" label="Title ({{ strtoupper($code) }})"
                        placeholder="e.g. ISO 9001:2015" :required="$code === $default"
                        @if ($code === $default) x-model="titleEn"
                        @else value="{{ old('title_' . $code, $credential->{'title_' . $code} ?? '') }}" @endif />

                    <x-admin.form.input name="issuer_{{ $code }}" label="Issuer ({{ strtoupper($code) }})"
                        placeholder="e.g. TÜV Rheinland"
                        value="{{ old('issuer_' . $code, $credential->{'issuer_' . $code} ?? '') }}" />

                    <x-admin.form.wysiwyg name="description_{{ $code }}"
                        label="Description ({{ strtoupper($code) }})"
                        :value="$credential->{'description_' . $code} ?? ''" />

                </div>
            @endforeach

            {{-- SLUG (single, generated from default-locale title) --}}
            <x-admin.form.input name="slug" label="URL Slug" x-model="slug" placeholder="auto-generated" readonly
                required />

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : ISSUANCE & VERIFICATION (not translated) --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Issuance & Verification</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Reference number, validity period and proof.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.input name="credential_number" label="Credential Number"
                value="{{ old('credential_number', $credential->credential_number ?? '') }}" />

            <x-admin.form.input name="verification_url" label="Verification URL" type="url"
                placeholder="https://…"
                value="{{ old('verification_url', $credential->verification_url ?? '') }}" />

            <x-admin.form.input name="issue_date" label="Issue Date" type="date"
                :value="old('issue_date', isset($credential) && $credential->issue_date ? $credential->issue_date->format('Y-m-d') : '')" />

            <x-admin.form.input name="expiry_date" label="Expiry Date" type="date"
                :value="old('expiry_date', isset($credential) && $credential->expiry_date ? $credential->expiry_date->format('Y-m-d') : '')" />

        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- IMAGE --}}
            <x-admin.image-preview name="image" label="Image / Certificate Preview"
                helpText="JPG/PNG/WebP. Max 2MB."
                :preview="isset($credential) && $credential->image ? asset('storage/' . $credential->image) : null" />

            {{-- ATTACHMENT (PDF) --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold tracking-wide text-gray-700">Certificate PDF</label>
                @if (isset($credential) && $credential->attachment)
                    <p class="mb-1 text-xs font-medium text-gray-500">
                        Current:
                        <a href="{{ asset('storage/' . $credential->attachment) }}" target="_blank" rel="noopener"
                            class="font-semibold text-equator-bright underline">view PDF</a>
                    </p>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600">
                        <input type="checkbox" name="remove_attachment" value="1"
                            class="rounded border-gray-300 text-equator-dark focus:ring-equator-bright/30">
                        Remove current PDF
                    </label>
                @endif
                <input type="file" name="attachment" accept="application/pdf"
                    class="block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-equator-dark/5 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-equator-dark hover:file:bg-equator-dark/10">
                <p class="text-xs font-medium text-gray-400">PDF only. Max 10MB.</p>
                @error('attachment')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : CLASSIFICATIONS / ITEMS (per-language repeater) --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 flex items-start justify-between gap-4 border-b border-gray-50 pb-4">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Classifications / Items</h2>
                <p class="mt-1 text-xs font-medium text-gray-500">Optional sub-items (e.g. LPJP service classes, KBLI
                    codes). Title is per-language. Use the language tabs above.</p>
            </div>
            <button type="button" @click="addItem()"
                class="shrink-0 rounded-xl bg-equator-dark/5 px-4 py-2 text-sm font-bold text-equator-dark transition hover:bg-equator-dark/10">
                + Add Item
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="(item, i) in items" :key="i">
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-400"
                            x-text="'Item ' + (i + 1)"></span>
                        <button type="button" @click="removeItem(i)"
                            class="text-xs font-semibold text-red-500 hover:text-red-700">Remove</button>
                    </div>

                    <input type="hidden" :name="`items[${i}][id]`" :value="item.id">
                    <input type="hidden" :name="`items[${i}][display_order]`" :value="i">

                    @foreach ($locales as $code => $meta)
                        <div x-show="locale === '{{ $code }}'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold tracking-wide text-gray-700">Title
                                    ({{ strtoupper($code) }})</label>
                                <input type="text" :name="`items[${i}][title_{{ $code }}]`"
                                    x-model="item.title_{{ $code }}"
                                    placeholder="e.g. KBLI 70209 — Management Consultancy"
                                    class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-equator-bright focus:ring-2 focus:ring-equator-bright/20">
                            </div>
                            <div>
                                <label class="block text-xs font-bold tracking-wide text-gray-700">Description
                                    ({{ strtoupper($code) }})</label>
                                <textarea :name="`items[${i}][description_{{ $code }}]`" x-model="item.description_{{ $code }}"
                                    rows="2"
                                    class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-equator-bright focus:ring-2 focus:ring-equator-bright/20"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </template>

            {{-- IDs of removed existing items, submitted for deletion --}}
            <template x-for="id in deletedItems" :key="'del-' + id">
                <input type="hidden" name="deleted_items[]" :value="id">
            </template>

            <p x-show="items.length === 0" class="text-sm font-medium text-gray-400">No items — this credential has no
                sub-classifications.</p>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 4 : PUBLISHING (not translated) --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Publishing</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status" required>
                <option value="active" {{ old('status', $credential->status ?? 'active') === 'active' ? 'selected' : '' }}>
                    Active</option>
                <option value="inactive"
                    {{ old('status', $credential->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </x-admin.form.select>

            <x-admin.form.input name="display_order" label="Display Order" type="number"
                value="{{ old('display_order', $credential->display_order ?? 1) }}" />

            <div class="md:col-span-2">
                <x-admin.form.toggle name="featured" label="Featured"
                    :checked="old('featured', $credential->featured ?? false)">
                    Featured credentials appear in the homepage "Trusted Credentials" section.
                </x-admin.form.toggle>
            </div>

        </div>
    </div>

</div>
