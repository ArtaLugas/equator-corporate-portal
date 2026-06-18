@extends('admin.layouts.app')

@section('title', 'Email Settings')
@section('page-title', 'Email Settings')

@section('content')

    <div class="mx-auto max-w-3xl">

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Email Settings (Brevo SMTP)</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Outgoing mail configuration. Used for contact notifications and replies. Stored securely — never hardcoded.
            </p>
        </div>

        <form action="{{ route('admin.settings.email.update') }}" method="POST"
            class="space-y-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="mail_host" label="SMTP Host"
                    :value="old('mail_host', $settings->mail_host)" placeholder="smtp-relay.brevo.com" />
                <x-admin.form.input name="mail_port" label="SMTP Port" type="number"
                    :value="old('mail_port', $settings->mail_port)" placeholder="587" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="mail_username" label="SMTP Username"
                    :value="old('mail_username', $settings->mail_username)" placeholder="your-brevo-login" />

                <div class="space-y-1.5">
                    <label for="mail_password" class="block text-xs font-bold tracking-wide text-gray-700">SMTP Password / API Key</label>
                    <input type="password" id="mail_password" name="mail_password" autocomplete="new-password"
                        placeholder="{{ $settings->mail_password ? '•••••••• (leave blank to keep)' : 'Enter SMTP key' }}"
                        class="block h-11 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-equator-text shadow-sm focus:border-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/20">
                    <p class="text-xs font-medium text-gray-400">Leave blank to keep the current password.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <x-admin.form.select name="mail_encryption" label="Encryption">
                    <option value="tls" {{ old('mail_encryption', $settings->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('mail_encryption', $settings->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                </x-admin.form.select>

                <x-admin.form.input name="mail_from_address" label="From Address" type="email"
                    :value="old('mail_from_address', $settings->mail_from_address)" placeholder="no-reply@equatorgroup.id" />
                <x-admin.form.input name="mail_from_name" label="From Name"
                    :value="old('mail_from_name', $settings->mail_from_name)" placeholder="Equator Group" />
            </div>

            <div class="border-t border-gray-100 pt-6">
                <x-admin.form.input name="office_email" label="Office Inbox (receives new-message notifications)" type="email"
                    :value="old('office_email', $settings->office_email)" placeholder="office@equatorgroup.id" />
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-6 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                    Save Settings
                </button>
            </div>
        </form>

    </div>

@endsection
