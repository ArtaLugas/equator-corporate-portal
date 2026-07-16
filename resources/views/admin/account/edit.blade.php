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
                        helpText="Square image recommended. Max 2MB." :preview="$admin->avatar ? asset('storage/' . $admin->avatar) : null" />
                </div>

                {{-- FIELDS --}}
                <div class="space-y-6 md:col-span-2">
                    <x-admin.form.input name="name" label="Full Name" :value="old('name', $admin->name)" placeholder="Your name"
                        required />
                    <x-admin.form.input name="email" label="Email Address" type="email" :value="old('email', $admin->email)"
                        placeholder="you@example.com" required />

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

        {{-- ============================= TWO-FACTOR AUTH ============================= --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 border-b border-gray-50 pb-4">
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Two-Factor Authentication</h2>
                <p class="mt-1 text-xs font-medium text-gray-500">Add a second step at login using an authenticator app
                    (Google Authenticator, Authy, 1Password).</p>
            </div>

            @if ($admin->hasTwoFactorEnabled())
                {{-- ---------- ENABLED ---------- --}}
                <div class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mt-0.5 shrink-0 text-emerald-600">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">Two-factor authentication is ON</p>
                        <p class="text-xs font-medium text-emerald-700">Enabled
                            {{ $admin->two_factor_confirmed_at?->format('d M Y, H:i') }}.</p>
                    </div>
                </div>

                @if (session('twofa_show_codes') && !empty($admin->two_factor_recovery_codes))
                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="mb-2 text-xs font-bold text-amber-800">New recovery codes — store these now, they are
                            shown once. Each works once if you lose your device.</p>
                        <div class="grid grid-cols-2 gap-1 font-mono text-sm text-amber-900 sm:grid-cols-4">
                            @foreach ($admin->two_factor_recovery_codes as $c)
                                <span>{{ $c }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.account.2fa.recovery') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">Regenerate
                        recovery codes</button>
                </form>

                <form action="{{ route('admin.account.2fa.disable') }}" method="POST"
                    class="mt-6 border-t border-gray-50 pt-6">
                    @csrf
                    @method('DELETE')

                    <p class="mb-3 text-xs font-medium text-gray-500">
                        Disabling removes the extra protection. Confirm with your password.
                    </p>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="w-full sm:max-w-xs">
                            <x-admin.form.input name="current_password" label="Current Password" type="password" required />
                        </div>

                        <button type="submit"
                            class="inline-flex h-12 items-center justify-center whitespace-nowrap rounded-xl border border-rose-200 bg-rose-50 px-6 text-sm font-semibold text-rose-700 transition-colors duration-200 hover:border-rose-300 hover:bg-rose-100">
                            Disable 2FA
                        </button>
                    </div>
                </form>
            @elseif ($twoFactor)
                {{-- ---------- SETUP PENDING ---------- --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-[auto_1fr]">
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">1. Scan this QR code</p>
                        <div id="twofa-qr" class="inline-block rounded-xl border border-gray-200 bg-white p-3"></div>
                        <p class="mt-3 text-xs font-medium text-gray-500">Or enter this key manually:</p>
                        <p
                            class="mt-1 select-all break-all rounded-lg bg-gray-50 px-3 py-2 font-mono text-sm text-equator-text">
                            {{ $twoFactor['secret'] }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">2. Save your recovery codes
                        </p>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="mb-2 text-xs font-medium text-amber-800">Store these somewhere safe. Each can be used
                                once if you lose your device.</p>
                            <div class="grid grid-cols-2 gap-1 font-mono text-sm text-amber-900">
                                @foreach ($twoFactor['recovery'] as $c)
                                    <span>{{ $c }}</span>
                                @endforeach
                            </div>
                        </div>

                        <form action="{{ route('admin.account.2fa.confirm') }}" method="POST" class="mt-6">
                            @csrf

                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                                3. Enter the 6-digit code from your app
                            </p>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="w-full sm:max-w-[180px]">
                                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                                        placeholder="123456" required
                                        class="@error('code') border-red-500 @else border-gray-200 @enderror block w-full rounded-xl border bg-gray-50/50 px-4 py-3 text-center font-mono text-lg tracking-widest text-equator-text focus:border-equator-bright focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-bright/50">
                                </div>

                                <button type="submit"
                                    class="inline-flex h-12 items-center justify-center whitespace-nowrap rounded-xl bg-equator-dark px-6 text-sm font-semibold text-white transition-colors duration-200 hover:bg-equator-bright">
                                    Confirm &amp; Enable
                                </button>
                            </div>

                            @error('code')
                                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
                <script>
                    (function() {
                        if (typeof qrcode !== 'function') return; // manual key remains available
                        var qr = qrcode(0, 'M');
                        qr.addData(@json($twoFactor['uri']));
                        qr.make();
                        document.getElementById('twofa-qr').innerHTML = qr.createImgTag(5, 8);
                    })();
                </script>
            @else
                {{-- ---------- NOT ENABLED ---------- --}}
                <p class="mb-4 text-sm font-medium text-gray-500">Two-factor authentication is currently off for your
                    account.</p>
                <form action="{{ route('admin.account.2fa.enable') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Enable
                        Two-Factor Authentication</button>
                </form>
            @endif
        </div>

        {{-- ============================= ACCOUNT INFO ============================= --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 border-b border-gray-50 pb-4">
                <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Account Information</h2>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Role</p>
                    <span
                        class="mt-2 inline-flex items-center rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-bold capitalize text-equator-dark">
                        {{ \Illuminate\Support\Str::headline($admin->role) }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Status</p>
                    <div class="mt-2"><x-admin.status-badge :status="$admin->status" :dot="true" /></div>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Last Login</p>
                    <p class="mt-1.5 text-sm font-medium text-gray-900">
                        {{ $admin->last_login_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>

    </div>

@endsection
