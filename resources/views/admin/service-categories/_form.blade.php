<div x-data="{
    name: @js(old('name', $category->name ?? '')),
    slug: @js(old('slug', $category->slug ?? '')),

    generateSlug() {
        this.slug = this.name
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }
}" x-effect="generateSlug()">

    {{-- CARD 1: GENERAL INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Category Information</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Core details of the service that will be displayed
                publicly.</p>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- NAME --}}
                <x-admin.form.input name="name" label="Category Name" x-model="name"
                    placeholder="e.g. Topographic Mapping" required />

                {{-- SLUG --}}
                <x-admin.form.input name="slug" label="URL Slug" x-model="slug" readonly required />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-admin.form.wysiwyg name="description" label="Category Description" :value="old('description', $category->description ?? '')" />
                </div>
            </div>

            {{-- IMAGE UPLOAD --}}
            <div class="pt-2">
                <x-admin.image-preview name="image" label="Category Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB." :preview="isset($category) && $category->image ? asset('storage/' . $category->image) : null" />
            </div>
        </div>
    </div>

    {{-- CARD 2: SEO SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Search Engine Optimization (SEO)</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Configure meta tags so this category is easy to find on
                search engines.</p>
        </div>

        <div class="space-y-6">
            {{-- META TITLE --}}
            <x-admin.form.input name="meta_title" label="Meta Title" :value="old('meta_title', $category->meta_title ?? '')"
                placeholder="Maximum 60 characters" />

            {{-- META DESCRIPTION --}}
            <div class="space-y-1.5">
                <label for="meta_description" class="block text-xs font-bold tracking-wide text-gray-700">
                    Meta Description
                </label>
                <textarea id="meta_description" name="meta_description" rows="4" @class([
                    'block w-full rounded-xl border px-4 py-3 text-sm text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:bg-gray-50',
                    'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                        'meta_description'),
                    'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                        'meta_description'),
                ])
                    placeholder="Write a brief summary for search engine results (Max 160 characters)...">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>

                @error('meta_description')
                    <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="mt-0.5 shrink-0">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" x2="12" y1="8" y2="12" />
                            <line x1="12" x2="12" y1="16" y2="16" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- META KEYWORDS --}}
            <x-admin.form.input name="meta_keywords" label="Meta Keywords" :value="old('meta_keywords', $category->meta_keywords ?? '')"
                placeholder="e.g. mapping, survey, drone, topography" />
        </div>
    </div>

    {{-- CARD 3: VISIBILITY & STATUS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Visibility Settings</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Set the display order and availability of this category on
                the public site.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">

                <option value="inactive" {{ old('status', $category->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive</option>
                <option value="active" {{ old('status', $category->status ?? '') == 'active' ? 'selected' : '' }}>Active
                </option>

            </x-admin.form.select>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order (Sort Order)" :value="old('display_order', $category->display_order ?? 0)"
                placeholder="Lower numbers appear first" min="1" required />
        </div>
    </div>

</div>
