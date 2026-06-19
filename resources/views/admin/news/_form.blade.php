@php
    $currentTags = old('tags', isset($news) ? $news->tags->pluck('name')->all() : []);
@endphp

<div x-data="{
    title: @js(old('title', $news->title ?? '')),
    slug: @js(old('slug', $news->slug ?? '')),
    generateSlug() {
        this.slug = this.title.toString().toLowerCase().trim()
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

            {{-- CATEGORY --}}
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

            {{-- TITLE + SLUG --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="title" label="Title" x-model="title"
                    placeholder="e.g. Company wins national award" required />
                <x-admin.form.input name="slug" label="URL Slug" x-model="slug"
                    placeholder="auto-generated" readonly required />
            </div>

            {{-- CONTENT --}}
            <x-admin.form.wysiwyg name="content" label="Content"
                :value="old('content', $news->content ?? '')" />

            {{-- IMAGE --}}
            <div class="pt-2">
                <x-admin.image-preview name="image" label="Featured Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB."
                    :preview="isset($news) && $news->image ? asset('storage/' . $news->image) : null" />
            </div>

            {{-- TAGS --}}
            <div class="space-y-1.5"
                x-data="{
                    tags: @js($currentTags),
                    input: '',
                    add() {
                        let v = this.input.replace(/,/g, '').trim();
                        if (v && !this.tags.includes(v)) this.tags.push(v);
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
                        @blur="add()"
                        placeholder="Type a tag and press Enter..."
                        class="flex-1 border-0 bg-transparent px-1 py-1 text-sm text-equator-text placeholder-gray-400 focus:outline-none focus:ring-0">
                </div>

                <p class="text-xs font-medium text-gray-400">Press Enter or comma to add. New tags are created automatically.</p>
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : SEO --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Search Engine Optimization (SEO)</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Configure metadata for better search visibility.</p>
        </div>

        <div class="space-y-6">
            <x-admin.form.input name="meta_title" label="Meta Title"
                :value="old('meta_title', $news->meta_title ?? '')" placeholder="Maximum 60 characters" />
            <x-admin.form.textarea name="meta_description" label="Meta Description" rows="3"
                :value="old('meta_description', $news->meta_description ?? '')" placeholder="Brief summary for search engines..." />
            <x-admin.form.input name="meta_keywords" label="Meta Keywords"
                :value="old('meta_keywords', $news->meta_keywords ?? '')" placeholder="e.g. award, company, milestone" />
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : PUBLISHING --}}
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
