<div class="space-y-6">

    {{-- ===================================================== --}}
    {{-- CARD 1 : MEMBER INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Member Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Core details about the team member that will be published publicly.
            </p>

        </div>

        <div class="space-y-6">

            {{-- NAME + POSITION --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <x-admin.form.input name="name" label="Full Name"
                    :value="old('name', $team->name ?? '')" placeholder="e.g. John Doe" required />

                <x-admin.form.input name="position" label="Position"
                    :value="old('position', $team->position ?? '')" placeholder="e.g. Chief Executive Officer" required />

            </div>

            {{-- EMAIL + LINKEDIN --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <x-admin.form.input name="email" label="Email Address" type="email"
                    :value="old('email', $team->email ?? '')" placeholder="e.g. john@example.com" />

                <x-admin.form.input name="linkedin_url" label="LinkedIn URL" type="url"
                    :value="old('linkedin_url', $team->linkedin_url ?? '')" placeholder="https://linkedin.com/in/username" />

            </div>

            {{-- BIO --}}
            <div>

                <x-admin.form.textarea name="bio" label="Biography" rows="5"
                    :value="old('bio', $team->bio ?? '')" placeholder="Short professional biography about this member..." />

            </div>

            {{-- PHOTO --}}
            <div class="pt-2">

                <x-admin.image-preview name="photo" label="Member Photo"
                    helpText="Square (1:1) ratio recommended. Max 2MB."
                    :preview="isset($team) && $team->photo ? asset('storage/' . $team->photo) : null" />

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
                :value="old('display_order', $team->display_order ?? 1)" placeholder="1" />

            <x-admin.form.select name="status" label="Status" required>

                <option value="active" {{ old('status', $team->status ?? 'active') == 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="inactive" {{ old('status', $team->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </x-admin.form.select>

        </div>

    </div>

</div>
