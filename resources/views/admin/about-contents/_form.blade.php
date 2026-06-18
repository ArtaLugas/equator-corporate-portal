<div class="space-y-8">

    {{-- GENERAL --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                About Content Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure about section content for public website.
            </p>
        </div>

        <div class="space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <x-admin.form.select name="section_id" label="About Section">

                    <option value="">
                        Select Section
                    </option>

                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(old('section_id', $content->section_id ?? '') == $section->id)>

                            {{ $section->name }}

                        </option>
                    @endforeach

                </x-admin.form.select>

                <x-admin.form.input name="title" label="Title" :value="old('title', $content->title ?? '')" placeholder="Enter content title" />

            </div>

            <div>

                <x-admin.form.input name="key" label="Key (identifier)" :value="old('key', $content->key ?? '')"
                    readonly tabindex="-1" class="bg-gray-50 cursor-not-allowed text-gray-500"
                    placeholder="Otomatis dari Title" />

                <p class="mt-1 text-xs font-medium text-gray-500">
                    Dibuat otomatis dari Title (huruf kecil, angka, underscore) dan bersifat <strong>read-only</strong>.
                    Key hanya berubah ketika Anda mengubah Title.
                </p>

            </div>

            <div>

                <x-admin.form.wysiwyg name="content" label="Content" :value="old('content', $content->content ?? '')" />

            </div>

            <div class="pt-2">

                <x-admin.image-preview name="image" label="Content Image" helpText="Recommended 16:9 image."
                    :preview="isset($content) && $content->image ? asset('storage/' . $content->image) : null" />

            </div>

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

                <option value="inactive" {{ old('status', $content->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

                <option value="active" {{ old('status', $content->status ?? '') == 'active' ? 'selected' : '' }}>
                    Active
                </option>

            </x-admin.form.select>

            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $content->display_order ?? 1)"
                placeholder="Lower numbers appear first" />

        </div>

    </div>

</div>

{{-- Auto-generate Key dari Title (live). Field Key read-only → selalu mengikuti Title. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const titleEl = document.getElementById('title');
        const keyEl = document.getElementById('key');

        if (!titleEl || !keyEl) {
            return;
        }

        const slugifyKey = (value) => value
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_') // karakter spesial/spasi -> underscore
            .replace(/_{2,}/g, '_')      // rapatkan underscore ganda
            .replace(/^_+|_+$/g, '');    // buang underscore di tepi

        // Key hanya diperbarui saat Title diubah (tidak ada input manual karena read-only).
        titleEl.addEventListener('input', function () {
            keyEl.value = slugifyKey(titleEl.value);
        });
    });
</script>
