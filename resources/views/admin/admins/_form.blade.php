@php $isEdit = isset($admin); @endphp

<div class="space-y-6">

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Account Details</h2>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            {{-- AVATAR --}}
            <div class="md:col-span-1">
                <x-admin.image-preview name="avatar" label="Avatar"
                    helpText="Square image. Max 2MB."
                    :preview="$isEdit && $admin->avatar ? asset('storage/' . $admin->avatar) : null" />
            </div>

            <div class="space-y-6 md:col-span-2">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-admin.form.input name="name" label="Full Name"
                        :value="old('name', $admin->name ?? '')" placeholder="Jane Doe" required />
                    <x-admin.form.input name="email" label="Email" type="email"
                        :value="old('email', $admin->email ?? '')" placeholder="jane@example.com" required />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-admin.form.select name="role" label="Role" required>
                        <option value="admin" {{ old('role', $admin->role ?? 'admin') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role', $admin->role ?? '') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </x-admin.form.select>

                    <x-admin.form.select name="status" label="Status" required>
                        <option value="active" {{ old('status', $admin->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $admin->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </x-admin.form.select>
                </div>

                @if ($isEdit && $admin->id === auth('admin')->id())
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                        You are editing your own account — role and status changes are disabled to prevent lockout.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- PASSWORD --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                {{ $isEdit ? 'Change Password' : 'Password' }}
            </h2>
            @if ($isEdit)
                <p class="mt-1 text-xs font-medium text-gray-500">Leave blank to keep the current password.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-admin.form.input name="password" label="Password" type="password" :required="!$isEdit" />
            <x-admin.form.input name="password_confirmation" label="Confirm Password" type="password" :required="!$isEdit" />
        </div>
    </div>

</div>
