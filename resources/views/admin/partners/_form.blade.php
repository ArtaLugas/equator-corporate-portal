<div class="space-y-6">

    {{-- ===================================================== --}}
    {{-- CARD 1 : PARTNER INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Partner Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Core details about the partner displayed on the public site.
            </p>

        </div>

        <div class="space-y-6">

            {{-- NAME + WEBSITE --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <x-admin.form.input name="name" label="Partner Name"
                    :value="old('name', $partner->name ?? '')" placeholder="e.g. Acme Corporation" required />

                <x-admin.form.input name="website" label="Website URL" type="url"
                    :value="old('website', $partner->website ?? '')" placeholder="https://example.com" />

            </div>

            {{-- LOGO --}}
            <div class="pt-2">

                <x-admin.image-preview name="logo" label="Partner Logo"
                    helpText="Transparent PNG or SVG recommended. Max 2MB."
                    :preview="isset($partner) && $partner->logo ? asset('storage/' . $partner->logo) : null" />

            </div>

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : VISIBILITY SETTINGS --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure publishing status and display order on the public page.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.input name="display_order" label="Display Order" type="number" min="1"
                :value="old('display_order', $partner->display_order ?? 1)" placeholder="1" />

            <x-admin.form.select name="status" label="Status" required>

                <option value="active" {{ old('status', $partner->status ?? 'active') == 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="inactive" {{ old('status', $partner->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </x-admin.form.select>

        </div>

    </div>

</div>
