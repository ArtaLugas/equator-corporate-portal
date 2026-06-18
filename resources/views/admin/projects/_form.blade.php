<div x-data="{
    name: @js(old('name', $project->name ?? '')),
    slug: @js(old('slug', $project->slug ?? '')),
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
}" x-effect="generateSlug()" class="space-y-6">

    {{-- ===================================================== --}}
    {{-- CARD 1 : PROJECT INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Project Information</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Core details about the project.</p>
        </div>

        <div class="space-y-6">

            {{-- SERVICE SCOPE (many-to-many) — pemilihan via MODAL agar form tetap ringkas --}}
            <div>
                @php
                    $allServices = $services->map(fn($s) => ['id' => (int) $s->id, 'name' => $s->name])->values();
                    $selectedServices = collect(
                        old('service_ids', isset($project) ? $project->services->pluck('id')->all() : []),
                    )
                        ->map(fn($v) => (int) $v)
                        ->values();
                @endphp

                <label class="block text-xs font-bold tracking-wide text-gray-700">
                    Service Scope <span class="text-rose-500">*</span>
                </label>

                @if ($services->isEmpty())
                    <p class="mt-2 text-xs font-semibold text-amber-600">
                        No services yet —
                        <a href="{{ route('admin.services.create') }}" class="underline">create one first</a>.
                    </p>
                @else
                    <div x-data="{
                        open: false,
                        q: '',
                        all: @js($allServices),
                        selected: @js($selectedServices),
                        get filtered() { const k = this.q.toLowerCase(); return this.all.filter(s => s.name.toLowerCase().includes(k)); },
                        get chosen() { return this.all.filter(s => this.selected.includes(s.id)); },
                        has(id) { return this.selected.includes(id); },
                        toggle(id) { this.has(id) ? (this.selected = this.selected.filter(x => x !== id)) : this.selected.push(id); },
                        remove(id) { this.selected = this.selected.filter(x => x !== id); },
                    }" class="mt-2">

                        {{-- Hidden inputs untuk submit --}}
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="service_ids[]" :value="id">
                        </template>

                        {{-- Trigger + chips terpilih --}}
                        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-2.5">
                            <template x-for="s in chosen" :key="s.id">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-semibold text-equator-dark">
                                    <span x-text="s.name"></span>
                                    <button type="button" @click="remove(s.id)"
                                        class="text-equator-dark/50 transition-colors hover:text-red-500"
                                        aria-label="Remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <span x-show="selected.length === 0" class="px-1 text-xs text-gray-400">Belum ada service
                                dipilih</span>

                            <button type="button" @click="open = true; q = ''"
                                class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-equator-dark transition-colors hover:border-equator-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                <span x-text="selected.length ? 'Edit Services' : 'Select Services'"></span>
                            </button>
                        </div>

                        {{-- MODAL --}}
                        <div x-show="open" x-cloak @keydown.escape.window="open = false"
                            class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
                            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="open = false"></div>

                            <div class="relative z-10 flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100">

                                {{-- Header --}}
                                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                    <h3 class="text-sm font-extrabold text-equator-text">Select Services</h3>
                                    <button type="button" @click="open = false"
                                        class="text-gray-400 transition-colors hover:text-gray-700" aria-label="Close">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Search --}}
                                <div class="border-b border-gray-100 p-4">
                                    <input x-model="q" type="text" placeholder="Search service..."
                                        class="block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-equator-text focus:border-equator-dark focus:outline-none focus:ring-1 focus:ring-equator-dark">
                                </div>

                                {{-- List --}}
                                <div class="flex-1 overflow-y-auto p-3">
                                    <template x-for="s in filtered" :key="s.id">
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-equator-text transition-colors hover:bg-gray-50">
                                            <input type="checkbox" :checked="has(s.id)" @change="toggle(s.id)"
                                                class="rounded border-gray-300 text-equator-dark focus:ring-equator-dark">
                                            <span x-text="s.name"></span>
                                        </label>
                                    </template>
                                    <p x-show="filtered.length === 0" class="px-3 py-6 text-center text-sm text-gray-400">
                                        No service found.</p>
                                </div>

                                {{-- Footer --}}
                                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4">
                                    <span class="text-xs font-medium text-gray-500"><span
                                            x-text="selected.length"></span> selected</span>
                                    <button type="button" @click="open = false"
                                        class="rounded-xl bg-equator-dark px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-white transition-colors hover:bg-equator-bright">Done</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @error('service_ids')
                    <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
                @error('service_ids.*')
                    <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- NAME + SLUG --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="name" label="Project Name" x-model="name"
                    placeholder="e.g. Coastal Mapping Survey" required />
                <x-admin.form.input name="slug" label="URL Slug" x-model="slug"
                    placeholder="e.g. coastal-mapping-survey" readonly required />
            </div>

            {{-- CLIENT + LOCATION + COUNTRY --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <x-admin.form.input name="client_name" label="Client Name"
                    :value="old('client_name', $project->client_name ?? '')" placeholder="e.g. PT Maju Jaya" />
                <x-admin.form.input name="location" label="Location"
                    :value="old('location', $project->location ?? '')" placeholder="e.g. Surabaya" />
                <x-admin.form.input name="country" label="Country"
                    :value="old('country', $project->country ?? '')" placeholder="e.g. Indonesia" />
            </div>

            {{-- DATES --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="start_date" label="Start Date" type="date"
                    :value="old('start_date', isset($project) && $project->start_date ? $project->start_date->format('Y-m-d') : '')" />
                <x-admin.form.input name="end_date" label="End Date" type="date"
                    :value="old('end_date', isset($project) && $project->end_date ? $project->end_date->format('Y-m-d') : '')" />
            </div>

            {{-- SHORT DESCRIPTION --}}
            <x-admin.form.textarea name="short_description" label="Short Description" rows="3"
                :value="old('short_description', $project->short_description ?? '')"
                placeholder="Brief summary (max 255 characters)..." />

            {{-- DESCRIPTION --}}
            <x-admin.form.wysiwyg name="description" label="Project Description"
                :value="old('description', $project->description ?? '')" />

            {{-- FEATURED IMAGE --}}
            <div class="pt-2">
                <x-admin.image-preview name="featured_image" label="Featured Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB."
                    :preview="isset($project) && $project->featured_image ? asset('storage/' . $project->featured_image) : null" />
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : GALLERY --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Project Gallery</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Add multiple images. Captions and order can be edited after upload.</p>
        </div>

        {{-- EXISTING IMAGES (edit only) --}}
        @if (isset($project) && $project->images->isNotEmpty())
            <div class="mb-8 space-y-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Existing Images</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($project->images as $image)
                        <div x-data="{ remove: false }"
                            class="flex gap-4 rounded-xl border p-3 transition-colors"
                            :class="remove ? 'border-red-200 bg-red-50/50' : 'border-gray-200 bg-gray-50/50'">

                            {{-- THUMB (16:10) --}}
                            <div class="aspect-[16/10] w-32 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white">
                                @if ($image->image)
                                    <img src="{{ asset('storage/' . $image->image) }}"
                                        class="h-full w-full object-cover" :class="remove ? 'opacity-40' : ''">
                                @endif
                            </div>

                            {{-- META --}}
                            <div class="flex flex-1 flex-col gap-2">
                                <input type="text" name="images[{{ $image->id }}][caption]"
                                    value="{{ old('images.' . $image->id . '.caption', $image->caption) }}"
                                    placeholder="Caption (optional)" :disabled="remove"
                                    class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-equator-text focus:border-equator-bright focus:outline-none focus:ring-1 focus:ring-equator-bright/30 disabled:opacity-50">

                                <div class="flex items-center gap-2">
                                    <input type="number" min="0" name="images[{{ $image->id }}][display_order]"
                                        value="{{ old('images.' . $image->id . '.display_order', $image->display_order) }}"
                                        placeholder="Order" :disabled="remove"
                                        class="w-20 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-equator-text focus:border-equator-bright focus:outline-none focus:ring-1 focus:ring-equator-bright/30 disabled:opacity-50">

                                    {{-- DELETE TOGGLE --}}
                                    <label class="flex cursor-pointer items-center gap-1.5 text-xs font-bold"
                                        :class="remove ? 'text-red-600' : 'text-gray-500'">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                            x-model="remove" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span x-text="remove ? 'Will be deleted' : 'Delete'"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- NEW UPLOADS --}}
        <div x-data="{
            files: [],
            handle(e) {
                this.files = Array.from(e.target.files).map(f => ({
                    name: f.name,
                    url: URL.createObjectURL(f),
                }));
            }
        }" class="space-y-3">

            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Add New Images</p>

            <label
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 transition-colors hover:border-gray-400 hover:bg-gray-100">
                <svg class="mb-2 h-7 w-7 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">
                    <span class="font-bold text-equator-dark">Click to upload</span> multiple images
                </p>
                <p class="mt-0.5 text-xs text-gray-400">JPG, PNG, WEBP — 16:10 ratio recommended, Max 2MB each</p>
                <input type="file" name="gallery_images[]" accept="image/*" multiple class="hidden" @change="handle">
            </label>

            {{-- PREVIEW --}}
            <div x-show="files.length" x-cloak class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <template x-for="(f, i) in files" :key="i">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                        <img :src="f.url" class="aspect-[16/10] w-full object-cover">
                    </div>
                </template>
            </div>

            @error('gallery_images.*')
                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : SEO --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Search Engine Optimization (SEO)</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Configure metadata for better search visibility.</p>
        </div>

        <div class="space-y-6">
            <x-admin.form.input name="meta_title" label="Meta Title"
                :value="old('meta_title', $project->meta_title ?? '')" placeholder="Maximum 60 characters" />
            <x-admin.form.textarea name="meta_description" label="Meta Description" rows="3"
                :value="old('meta_description', $project->meta_description ?? '')"
                placeholder="Brief summary for search engines..." />
            <x-admin.form.input name="meta_keywords" label="Meta Keywords"
                :value="old('meta_keywords', $project->meta_keywords ?? '')"
                placeholder="e.g. survey, mapping, lidar" />
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- CARD 4 : VISIBILITY --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Visibility Settings</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Configure project status and featured visibility.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status" required>
                <option value="planned" {{ old('status', $project->status ?? 'planned') == 'planned' ? 'selected' : '' }}>Planned</option>
                <option value="ongoing" {{ old('status', $project->status ?? '') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status', $project->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
            </x-admin.form.select>

            <x-admin.form.toggle name="is_featured" label="Featured Project"
                :checked="old('is_featured', $project->is_featured ?? false)">
                Featured projects appear on homepage sections.
            </x-admin.form.toggle>

        </div>
    </div>

</div>
