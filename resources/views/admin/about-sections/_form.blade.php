<div class="space-y-8" x-data="{

    name: @js(old('name', $aboutSection->name ?? '')),

    slug: @js(old('slug', $aboutSection->slug ?? '')),


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

    {{-- CARD 1 : SECTION INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        {{-- HEADER --}}
        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">

                About Section Information

            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">

                Manage section grouping structure for About page content modules.

            </p>

        </div>

        {{-- FORM --}}
        <div class="space-y-6">

            {{-- NAME & SLUG --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- NAME --}}
                <x-admin.form.input name="name" label="Section Name" x-model="name" placeholder="e.g. Company Profile"
                    required />

                {{-- SLUG --}}
                <x-admin.form.input name="slug" label="URL Slug" x-model="slug" placeholder="e.g. company-profile"
                    readonly required />

            </div>

        </div>

    </div>

    {{-- CARD 2 : VISIBILITY SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        {{-- HEADER --}}
        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">

                Visibility Settings

            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">

                Configure display order and publication visibility for this section.

            </p>

        </div>

        {{-- FORM --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- STATUS --}}
            <x-admin.form.select name="status" label="Status">

                <option value="inactive"
                    {{ old('status', $aboutSection->status ?? '') === 'inactive' ? 'selected' : '' }}>

                    Inactive

                </option>

                <option value="active" {{ old('status', $aboutSection->status ?? '') === 'active' ? 'selected' : '' }}>

                    Active

                </option>

            </x-admin.form.select>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $aboutSection->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
