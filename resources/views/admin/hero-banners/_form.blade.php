<div class="space-y-8">

    {{-- CARD : HERO INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Hero Banner Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Main banner content displayed on homepage hero section.
            </p>

        </div>

        <div class="space-y-6">

            {{-- TITLE --}}
            <x-admin.form.input name="title" label="Banner Title" :value="old('title', $banner->title ?? '')"
                placeholder="Example: Empowering Sustainable Development" />

            {{-- SUBTITLE --}}
            <div class="space-y-1.5">

                <label for="subtitle" class="block text-xs font-bold tracking-wide text-gray-700">

                    Subtitle

                </label>

                <textarea id="subtitle" name="subtitle" rows="4" @class([
                    'block w-full rounded-xl border px-4 py-3 text-sm text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1',
                
                    'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                        'subtitle'),
                
                    'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                        'subtitle'),
                ])
                    placeholder="Write banner subtitle here...">{{ old('subtitle', $banner->subtitle ?? '') }}</textarea>

                @error('subtitle')
                    <p class="text-xs font-semibold text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            {{-- IMAGE --}}
            <div class="pt-2">

                <x-admin.image-preview name="image" label="Banner Image" helpText="Recommended ratio 16:9. Max 2MB."
                    :preview="isset($banner) && $banner->image ? asset('storage/' . $banner->image) : null" />

            </div>

        </div>

    </div>

    {{-- CARD : BUTTON SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Button Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Optional call-to-action button configuration.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- BUTTON TEXT --}}
            <x-admin.form.input name="button_text" label="Button Text" :value="old('button_text', $banner->button_text ?? '')"
                placeholder="Example: Learn More" />

            {{-- BUTTON LINK --}}
            <x-admin.form.input name="button_link" label="Button Link" :value="old('button_link', $banner->button_link ?? '')"
                placeholder="https://example.com" />

        </div>

    </div>

    {{-- CARD : VISIBILITY --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Control homepage visibility and sorting order.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- STATUS --}}
            <div class="space-y-1.5">

                <label for="status" class="block text-xs font-bold tracking-wide text-gray-700">

                    Status

                </label>

                <div class="relative">

                    <select id="status" name="status" @class([
                        'appearance-none block w-full rounded-xl border px-4 py-2.5 text-sm font-bold text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-white cursor-pointer',
                    
                        'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                            'status'),
                    
                        'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                            'status'),
                    ])>

                        <option value="active"
                            {{ old('status', $banner->status ?? '') === 'active' ? 'selected' : '' }}>

                            Active

                        </option>

                        <option value="inactive"
                            {{ old('status', $banner->status ?? '') === 'inactive' ? 'selected' : '' }}>

                            Inactive

                        </option>

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

            </div>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $banner->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
