<div x-data="{

    name: @js(old('name', $service->name ?? '')),

    slug: @js(old('slug', $service->slug ?? '')),

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

    {{-- ===================================================== --}}
    {{-- CARD 1 : SERVICE INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Service Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Core details about the service that will be published publicly.
            </p>

        </div>

        <div class="space-y-6">

            {{-- CATEGORY --}}
            <div class="space-y-1.5">

                <label for="category_id" class="block text-xs font-bold tracking-wide text-gray-700">

                    Service Category

                </label>

                <div class="relative">

                    <select id="category_id" name="category_id" @class([
                        'appearance-none block w-full rounded-xl border px-4 py-2.5 text-sm font-bold text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-white cursor-pointer',
                    
                        'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                            'category_id'),
                    
                        'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                            'category_id'),
                    ])>

                        <option value="">
                            Select Category
                        </option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $service->category_id ?? '') == $category->id ? 'selected' : '' }}>

                                {{ $category->name }}

                            </option>
                        @endforeach

                    </select>

                    {{-- CHEVRON --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">

                            <path d="m6 9 6 6 6-6" />

                        </svg>

                    </div>

                </div>

                @error('category_id')
                    <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            {{-- NAME + SLUG --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- NAME --}}
                <x-admin.form.input name="name" label="Service Name" x-model="name"
                    placeholder="e.g. Topographic Survey" required />

                {{-- SLUG --}}
                <x-admin.form.input name="slug" label="URL Slug" x-model="slug" placeholder="e.g. topographic-survey"
                    readonly required />

            </div>

            {{-- SHORT DESCRIPTION --}}
            <div>

                <x-admin.form.textarea name="short_description" label="Short Description" rows="3"
                    :value="old('short_description', $service->short_description ?? '')" placeholder="Brief summary about this service..." />

            </div>

            {{-- DESCRIPTION --}}
            <div class="grid grid-cols-1 gap-6">

                <div>

                    <x-admin.form.wysiwyg name="description" label="Service Description" :value="old('description', $service->description ?? '')" />

                </div>

            </div>

            {{-- IMAGE --}}
            <div class="pt-2">

                <x-admin.image-preview name="image" label="Service Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB." :preview="isset($service) && $service->image ? asset('storage/' . $service->image) : null" />

            </div>

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : SEO SETTINGS --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Search Engine Optimization (SEO)
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure metadata for better search engine visibility.
            </p>

        </div>

        <div class="space-y-6">

            {{-- META TITLE --}}
            <x-admin.form.input name="meta_title" label="Meta Title" :value="old('meta_title', $service->meta_title ?? '')"
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
                    placeholder="Brief summary for search engines...">{{ old('meta_description', $service->meta_description ?? '') }}</textarea>

                @error('meta_description')
                    <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            {{-- META KEYWORDS --}}
            <x-admin.form.input name="meta_keywords" label="Meta Keywords" :value="old('meta_keywords', $service->meta_keywords ?? '')"
                placeholder="e.g. survey, mapping, drone, lidar" />

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : VISIBILITY SETTINGS --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure service publishing and featured visibility.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">

                <option value="draft" {{ old('status', $service->status ?? '') == 'draft' ? 'selected' : '' }}>
                    Draft
                </option>

                <option value="published" {{ old('status', $service->status ?? '') == 'published' ? 'selected' : '' }}>
                    Published
                </option>

            </x-admin.form.select>

            <x-admin.form.toggle name="is_featured" label="Featured Service" :checked="old('is_featured', $service->is_featured ?? false)">

                Featured services appear on homepage sections.

            </x-admin.form.toggle>

        </div>

    </div>

</div>
