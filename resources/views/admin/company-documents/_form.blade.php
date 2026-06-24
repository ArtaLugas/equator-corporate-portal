@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'description'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    // Slug auto-regeneration: always on for new records; on edit it follows config.
    $editing = isset($document) && $document->exists;
    $autoSlug = ! $editing || config('cms.auto_regenerate_slug', true);

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div x-data="{
    locale: @js($activeTab),
    autoSlug: @js($autoSlug),
    titleEn: @js(old('title_' . $default, $document->{'title_' . $default} ?? '')),
    slug: @js(old('slug', $document->slug ?? '')),
    generateSlug() {
        if (! this.autoSlug) return; // permalink frozen — keep the existing slug
        this.slug = this.titleEn.toString().toLowerCase().trim()
            .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
    }
}" x-effect="generateSlug()" class="space-y-8">

    <div class="space-y-8">
        {{-- GENERAL INFORMATION --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

            <div class="mb-6 border-b border-gray-50 pb-4">

                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                    Document Information
                </h2>

                <p class="mt-1 text-xs font-medium text-gray-500">
                    Upload and manage company documents.
                </p>

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

                {{-- LANGUAGE TABS (control title + description) --}}
                <x-admin.lang-tabs />

                {{-- TRANSLATABLE FIELDS — one panel per locale --}}
                @foreach ($locales as $code => $meta)
                    <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                        @if ($code === $default)
                            <x-admin.form.input
                                name="title_{{ $code }}"
                                label="Document Title ({{ strtoupper($code) }})"
                                required
                                x-model="titleEn"
                            />
                        @else
                            <x-admin.form.input
                                name="title_{{ $code }}"
                                label="Document Title ({{ strtoupper($code) }})"
                                value="{{ old('title_' . $code, $document->{'title_' . $code} ?? '') }}"
                            />
                        @endif

                        <x-admin.form.wysiwyg
                            name="description_{{ $code }}"
                            label="Description ({{ strtoupper($code) }})"
                            :value="old('description_' . $code, $document->{'description_' . $code} ?? '')" />

                    </div>
                @endforeach

                {{-- SLUG (not translated, read-only) --}}
                <x-admin.form.input name="slug" label="Slug" :value="old('slug', $document->slug ?? '')" x-model="slug" readonly
                    class="cursor-not-allowed bg-gray-50" />

                <x-admin.form.select name="document_type" label="Document Type">

                    <option value="">Select Type</option>

                    <option value="company_profile"
                        {{ old('document_type', $document->document_type ?? '') == 'company_profile' ? 'selected' : '' }}>
                        Company Profile
                    </option>

                    <option value="capability_statement"
                        {{ old('document_type', $document->document_type ?? '') == 'capability_statement' ? 'selected' : '' }}>
                        Capability Statement
                    </option>

                    <option value="corporate_brochure"
                        {{ old('document_type', $document->document_type ?? '') == 'corporate_brochure' ? 'selected' : '' }}>
                        Corporate Brochure
                    </option>

                    <option value="presentation"
                        {{ old('document_type', $document->document_type ?? '') == 'presentation' ? 'selected' : '' }}>
                        Presentation
                    </option>

                    <option value="other"
                        {{ old('document_type', $document->document_type ?? '') == 'other' ? 'selected' : '' }}>
                        Other
                    </option>

                </x-admin.form.select>

                <x-admin.form.file name="file" label="PDF Document" accept=".pdf" :currentFile="$document->file ?? null"
                    helpText="PDF only. Max 20MB." />

                <x-admin.image-preview name="thumbnail" label="Thumbnail" helpText="Optional thumbnail image."
                    :preview="isset($document) && $document->thumbnail
                        ? asset('storage/' . $document->thumbnail)
                        : null" />

            </div>

        </div>

        {{-- SETTINGS --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

            <div class="mb-6 border-b border-gray-50 pb-4">

                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                    Visibility Settings
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <x-admin.form.select name="status" label="Status">

                    <option value="active" {{ old('status', $document->status ?? '') == 'active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="inactive"
                        {{ old('status', $document->status ?? '') == 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>

                </x-admin.form.select>

                <x-admin.form.input type="number" name="display_order" min="1" label="Display Order"
                    :value="old('display_order', $document->display_order ?? 1)" />

            </div>

        </div>
    </div>

</div>
