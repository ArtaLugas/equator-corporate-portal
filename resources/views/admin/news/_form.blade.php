@php
    $currentTags = old('tags', isset($news) ? $news->tags->pluck('name')->all() : []);

    $locales = config('locales.supported', []);
    $default = config('locales.default');

    // Open the tab of the first locale that has a validation error.
    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'content', 'meta_title', 'meta_description', 'meta_keywords'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    // Slug auto-regeneration: always on for new records; on edit it follows config.
    $editing = isset($news) && $news->exists;
    $autoSlug = ! $editing || config('cms.auto_regenerate_slug', true);

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div x-data="{
    locale: @js($activeTab),
    autoSlug: @js($autoSlug),
    titleEn: @js(old('title_' . $default, $news->{'title_' . $default} ?? '')),
    slug: @js(old('slug', $news->slug ?? '')),
    generateSlug() {
        if (! this.autoSlug) return; // permalink frozen — keep the existing slug
        this.slug = this.titleEn.toString().toLowerCase().trim()
            .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
    }
}" x-effect="generateSlug()" class="space-y-6">

    {{-- ===================================================== --}}
    {{-- CARD 1 : ARTICLE --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Article</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Main content of the news article.</p>
        </div>

        <div class="space-y-6">

            {{-- CATEGORY (not translated) --}}
            <x-admin.form.select name="category_id" label="Category" required>
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $news->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-admin.form.select>

            @if ($categories->isEmpty())
                <p class="-mt-3 text-xs font-semibold text-amber-600">
                    No categories yet —
                    <a href="{{ route('admin.news-categories.create') }}" class="underline">create one first</a>.
                </p>
            @endif

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

            {{-- LANGUAGE TABS (control every translatable field) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    @if ($code === $default)
                        <x-admin.form.input
                            name="title_{{ $code }}"
                            label="Title ({{ strtoupper($code) }})"
                            placeholder="e.g. Company wins national award"
                            required
                            x-model="titleEn"
                        />
                    @else
                        <x-admin.form.input
                            name="title_{{ $code }}"
                            label="Title ({{ strtoupper($code) }})"
                            placeholder="e.g. Company wins national award"
                            value="{{ old('title_' . $code, $news->{'title_' . $code} ?? '') }}"
                        />
                    @endif

                    <x-admin.form.wysiwyg
                        name="content_{{ $code }}"
                        label="Content ({{ strtoupper($code) }})"
                        :value="$news->{'content_' . $code} ?? ''" />

                </div>
            @endforeach

            {{-- SLUG (single, generated from the default-locale title) --}}
            <x-admin.form.input name="slug" label="URL Slug" x-model="slug"
                placeholder="auto-generated" readonly required />

            {{-- IMAGE (not translated) --}}
            <div class="pt-2">
                <x-admin.image-preview name="image" label="Featured Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB."
                    :preview="isset($news) && $news->image ? asset('storage/' . $news->image) : null" />
            </div>

            {{-- TAGS (not translated) --}}
            <div class="space-y-1.5"
                x-data="{
                    tags: @js($currentTags),
                    input: '',
                    // Comma is the documented separator; newline/tab cover pastes out of
                    // a spreadsheet column and semicolon covers Word-style lists. None of
                    // them are plausible inside a tag name. No /g flag — test() would
                    // otherwise carry lastIndex between calls.
                    separators: /[,;\n\r\t]+/,
                    push(value) {
                        let v = value.trim();
                        if (! v) return;
                        // Case-insensitive: syncTags() keys tags by slug, so 'Survey' and
                        // 'survey' resolve to one row. Two chips would misrepresent what
                        // actually gets saved. First spelling pasted wins.
                        if (this.tags.some(t => t.toLowerCase() === v.toLowerCase())) return;
                        this.tags.push(v);
                    },
                    add() {
                        // Enter/comma/blur: split too, so a hand-typed 'a, b' also lands
                        // as two tags instead of one.
                        this.input.split(this.separators).forEach(v => this.push(v));
                        this.input = '';
                    },
                    paste(event) {
                        let text = (event.clipboardData || window.clipboardData).getData('text');
                        // Nothing to split — let it drop into the box so it stays editable.
                        if (! this.separators.test(text)) return;
                        event.preventDefault();
                        (this.input + text).split(this.separators).forEach(v => this.push(v));
                        this.input = '';
                    },
                    remove(i) { this.tags.splice(i, 1); }
                }">
                <label class="block text-xs font-bold tracking-wide text-gray-700">Tags</label>

                <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-2 focus-within:border-equator-bright focus-within:ring-2 focus-within:ring-equator-bright/20">

                    <template x-for="(tag, i) in tags" :key="i">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-bold text-equator-dark">
                            <span x-text="tag"></span>
                            <button type="button" @click="remove(i)" class="text-equator-dark/60 hover:text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>
                            <input type="hidden" name="tags[]" :value="tag">
                        </span>
                    </template>

                    <input type="text" x-model="input"
                        @keydown.enter.prevent="add()"
                        @keydown.comma.prevent="add()"
                        @paste="paste($event)"
                        @blur="add()"
                        placeholder="Type a tag, or paste a comma-separated list..."
                        class="flex-1 border-0 bg-transparent px-1 py-1 text-sm text-equator-text placeholder-gray-400 focus:outline-none focus:ring-0">
                </div>

                <p class="text-xs font-medium text-gray-400">Press Enter or comma to add, or paste a comma-separated
                    list to add them all at once. New tags are created automatically.</p>
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : SEO --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Search Engine Optimization (SEO)</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Per-language metadata. Use the language tabs above to switch.</p>
        </div>

        {{-- TRANSLATABLE SEO FIELDS — share the same locale tab as above --}}
        @foreach ($locales as $code => $meta)
            <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                <x-admin.form.input
                    name="meta_title_{{ $code }}"
                    label="Meta Title ({{ strtoupper($code) }})"
                    placeholder="Maximum 60 characters"
                    value="{{ old('meta_title_' . $code, $news->{'meta_title_' . $code} ?? '') }}" />

                <x-admin.form.textarea
                    name="meta_description_{{ $code }}"
                    label="Meta Description ({{ strtoupper($code) }})"
                    rows="3"
                    :value="$news->{'meta_description_' . $code} ?? ''"
                    placeholder="Brief summary for search engines..." />

                <x-admin.form.input
                    name="meta_keywords_{{ $code }}"
                    label="Meta Keywords ({{ strtoupper($code) }})"
                    placeholder="e.g. award, company, milestone"
                    value="{{ old('meta_keywords_' . $code, $news->{'meta_keywords_' . $code} ?? '') }}" />

            </div>
        @endforeach

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : PUBLISHING (not translated) --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Publishing</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Control status, schedule and visibility.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status" required>
                <option value="draft" {{ old('status', $news->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $news->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
            </x-admin.form.select>

            <x-admin.form.input name="published_at" label="Publish Date" type="datetime-local"
                :value="old('published_at', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '')" />

            <div class="md:col-span-2">
                <x-admin.form.toggle name="is_featured" label="Featured Article"
                    :checked="old('is_featured', $news->is_featured ?? false)">
                    Featured articles appear on homepage sections.
                </x-admin.form.toggle>
            </div>

        </div>

        <p class="mt-4 text-xs font-medium text-gray-400">
            Leave publish date empty — when status is "Published" it will be set automatically.
        </p>
    </div>

</div>
