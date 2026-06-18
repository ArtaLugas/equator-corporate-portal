@extends('admin.layouts.app')

@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@section('content')

    <div class="mx-auto max-w-4xl space-y-6">

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Account Settings</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage your personal profile, photo and password.</p>
        </div>

        {{-- ============================= PROFILE ============================= --}}
        <form action="{{ route('admin.account.profile.update') }}" method="POST" enctype="multipart/form-data"
            class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            <div class="mb-6 border-b border-gray-50 pb-4">
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Profile</h2>
                <p class="mt-1 text-xs font-medium text-gray-500">Your name, email and profile photo.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">

                {{-- AVATAR --}}
                <div class="md:col-span-1">
                    <x-admin.image-preview name="avatar" label="Profile Photo"
                        helpText="Square image recommended. Max 2MB."
                        :preview="$admin->avatar ? asset('storage/' . $admin->avatar) : null" />
                </div>

                {{-- FIELDS --}}
                <div class="space-y-6 md:col-span-2">
                    <x-admin.form.input name="name" label="Full Name"
                        :value="old('name', $admin->name)" placeholder="Your name" required />
                    <x-admin.form.input name="email" label="Email Address" type="email"
                        :value="old('email', $admin->email)" placeholder="you@example.com" required />

                    <div class="flex justify-end">
                        <button type="submit"
                            class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                            Save Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ============================= PASSWORD ============================= --}}
        <form action="{{ route('admin.account.password.update') }}" method="POST"
            class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            <div class="mb-6 border-b border-gray-50 pb-4">
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Change Password</h2>
                <p class="mt-1 text-xs font-medium text-gray-500">Use a strong password you don't reuse elsewhere.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="md:col-span-2 md:max-w-md">
                    <x-admin.form.input name="current_password" label="Current Password" type="password" required />
                </div>
                <x-admin.form.input name="password" label="New Password" type="password" required />
                <x-admin.form.input name="password_confirmation" label="Confirm New Password" type="password" required />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                    Update Password
                </button>
            </div>
        </form>

        {{-- ============================= ACCOUNT INFO ============================= --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 border-b border-gray-50 pb-4">
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Account Information</h2>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Role</p>
                    <span class="mt-2 inline-flex items-center rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-bold capitalize text-equator-dark">
                        {{ \Illuminate\Support\Str::headline($admin->role) }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Status</p>
                    <div class="mt-2"><x-admin.status-badge :status="$admin->status" :dot="true" /></div>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Last Login</p>
                    <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $admin->last_login_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>

    </div>

@endsection
