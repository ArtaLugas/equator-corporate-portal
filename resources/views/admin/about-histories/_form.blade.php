<div class="space-y-8">

    {{-- GENERAL INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                History Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Manage company timeline and milestone information.
            </p>

        </div>

        <div class="space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- YEAR --}}
                <x-admin.form.input type="number" name="year" label="Year" :value="old('year', $aboutHistory->year ?? date('Y'))" min="1900"
                    max="{{ date('Y') + 10 }}" required />

                {{-- TITLE --}}
                <x-admin.form.input name="title" label="Title" :value="old('title', $aboutHistory->title ?? '')"
                    placeholder="Example: Company Founded" required />

            </div>

            {{-- DESCRIPTION --}}
            <div>

                <x-admin.form.wysiwyg name="description" label="Description" :value="old('description', $aboutHistory->description ?? '')" />

            </div>

            {{-- IMAGE --}}
            <div class="pt-2">

                <x-admin.image-preview name="image" label="Timeline Image"
                    helpText="Optional image for timeline milestone." :preview="isset($aboutHistory) && $aboutHistory->image
                        ? asset('storage/' . $aboutHistory->image)
                        : null" />

            </div>

        </div>

    </div>

    {{-- VISIBILITY SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure display order and publication status.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- STATUS --}}
            <x-admin.form.select name="status" label="Status">

                <option value="active"
                    {{ old('status', $aboutHistory->status ?? 'active') === 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="inactive"
                    {{ old('status', $aboutHistory->status ?? '') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </x-admin.form.select>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $aboutHistory->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
