<div x-data="{

    title: '{{ old('title', $document->title ?? '') }}',

    slug: '{{ old('slug', $document->slug ?? '') }}',

    isEdit: {{ isset($document) ? 'true' : 'false' }},

    generateSlug() {

        if (this.isEdit) return;

        this.slug = this.title

            .toString()

            .toLowerCase()

            .trim()

            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');

    }

}" class="space-y-8">

    <div class="space-y-8">
        {{-- GENERAL INFORMATION --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

            <div class="mb-6 border-b border-gray-50 pb-4">

                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                    Document Information
                </h2>

                <p class="mt-1 text-xs font-medium text-gray-500">
                    Upload and manage company documents.
                </p>

            </div>

            <div class="space-y-6">

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <x-admin.form.input name="title" label="Document Title" :value="old('title', $document->title ?? '')" x-model="title"
                        @input="generateSlug" required />

                    <x-admin.form.input name="slug" label="Slug" :value="old('slug', $document->slug ?? '')" x-model="slug" readonly
                        class="cursor-not-allowed bg-gray-50" />

                </div>

                <x-admin.form.select name="document_type" label="Document Type">

                    <option value="">Select Type</option>

                    <option value="company_profile"
                        {{ old('document_type', $document->document_type ?? '') == 'company_profile' ? 'selected' : '' }}>
                        Company Profile
                    </option>

                    <option value="capability_statement"
                        {{ old('document_type', $document->document_type ?? '') == 'capability_statement' ? 'selected' : '' }}>
                        Capability Statement
                    </option>

                    <option value="corporate_brochure"
                        {{ old('document_type', $document->document_type ?? '') == 'corporate_brochure' ? 'selected' : '' }}>
                        Corporate Brochure
                    </option>

                    <option value="presentation"
                        {{ old('document_type', $document->document_type ?? '') == 'presentation' ? 'selected' : '' }}>
                        Presentation
                    </option>

                    <option value="other"
                        {{ old('document_type', $document->document_type ?? '') == 'other' ? 'selected' : '' }}>
                        Other
                    </option>

                </x-admin.form.select>

                <x-admin.form.wysiwyg name="description" label="Description" :value="old('description', $document->description ?? '')" />

                <x-admin.form.file name="file" label="PDF Document" accept=".pdf" :currentFile="$document->file ?? null"
                    helpText="PDF only. Max 20MB." />

                <x-admin.image-preview name="thumbnail" label="Thumbnail" helpText="Optional thumbnail image."
                    :preview="isset($document) && $document->thumbnail
                        ? asset('storage/' . $document->thumbnail)
                        : null" />

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

                    <option value="active" {{ old('status', $document->status ?? '') == 'active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="inactive"
                        {{ old('status', $document->status ?? '') == 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>

                </x-admin.form.select>

                <x-admin.form.input type="number" name="display_order" min="1" label="Display Order"
                    :value="old('display_order', $document->display_order ?? 1)" />

            </div>

        </div>
