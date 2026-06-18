<div class="space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Office Location</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A contact point (head office or branch) shown on the public
                Contact page & footer.</p>
        </div>

        <div class="space-y-6">

            <x-admin.form.input name="name" label="Location Name"
                :value="old('name', $location->name ?? '')" placeholder="e.g. Head Office — Jakarta" required />

            <x-admin.form.textarea name="address" label="Address" rows="3" placeholder="Full street address"
                :value="$location->address ?? ''" />

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="phone" label="Phone"
                    :value="old('phone', $location->phone ?? '')" placeholder="+62 21 1234 5678" />
                <x-admin.form.input type="email" name="email" label="Email"
                    :value="old('email', $location->email ?? '')" placeholder="office@company.com" />
            </div>

            <div>
                <x-admin.form.textarea name="map_embed" label="Google Maps Embed (iframe)" rows="3"
                    placeholder="Paste the full Google Maps <iframe> embed code here"
                    :value="$location->map_embed ?? ''" />
                <p class="mt-1.5 text-xs font-medium text-gray-400">Google Maps → Share → Embed a map → copy the full
                    <code>&lt;iframe&gt;</code> code.</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Visibility Settings</h2>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input type="number" name="display_order" label="Display Order" min="0"
                    :value="old('display_order', $location->display_order ?? 1)" placeholder="Lower numbers appear first" />
                <x-admin.form.select name="status" label="Status" required>
                    <option value="active" {{ old('status', $location->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $location->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </x-admin.form.select>
            </div>

            <x-admin.form.toggle name="is_primary" label="Primary Location"
                :checked="(bool) old('is_primary', $location->is_primary ?? false)">
                Lokasi utama — ditampilkan di footer & dipakai sebagai kontak utama. Hanya boleh satu; memilih ini
                otomatis menonaktifkan primary pada lokasi lain.
            </x-admin.form.toggle>
        </div>
    </div>
</div>
