@extends('admin.layouts.app')

@section('title', 'General Settings')
@section('page-title', 'General Settings')

@section('content')

    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">General Settings</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Company profile, branding, contact details and default SEO.</p>
        </div>

        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- COMPANY PROFILE --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 border-b border-gray-50 pb-4">
                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Company Profile</h2>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <x-admin.form.input name="company_name" label="Company Name"
                            :value="old('company_name', $settings->company_name)" placeholder="Equator Group" />
                        <x-admin.form.input name="tagline" label="Tagline"
                            :value="old('tagline', $settings->tagline)" placeholder="Your trusted partner" />
                    </div>

                    {{-- LOGO + FAVICON --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        @foreach (['logo' => 'Logo', 'favicon' => 'Favicon'] as $field => $label)
                            <div x-data="{ removed: false, preview: @js($settings->{$field} ? asset('storage/' . $settings->{$field}) : null) }"
                                class="space-y-2">
                                <label class="block text-xs font-bold tracking-wide text-gray-700">{{ $label }}</label>

                                <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <template x-if="preview && !removed"><img :src="preview" class="h-full w-full object-contain"></template>
                                        <template x-if="!preview || removed">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-gray-300"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                        </template>
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <input type="file" name="{{ $field }}" accept="image/*"
                                            @change="preview = URL.createObjectURL($event.target.files[0]); removed = false"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-equator-dark file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-equator-bright">
                                        @if ($settings->{$field})
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                                <input type="checkbox" name="remove_{{ $field }}" value="1" x-model="removed"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                Remove current {{ $label }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                @error($field)<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- CONTACT — dipindahkan ke Office Locations (sumber tunggal) --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 border-b border-gray-50 pb-4">
                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Contact Information</h2>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mt-0.5 shrink-0 text-blue-500">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <p>
                        Address, phone, email and map are now managed under
                        <a href="{{ route('admin.office-locations.index') }}"
                            class="font-bold underline hover:text-blue-900">Office Locations</a>.
                        This keeps the website, footer and contact page in sync from a single source.
                    </p>
                </div>
            </div>

            {{-- DEFAULT SEO --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 border-b border-gray-50 pb-4">
                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Default SEO</h2>
                </div>
                <div class="space-y-6">
                    <x-admin.form.input name="meta_title" label="Meta Title"
                        :value="old('meta_title', $settings->meta_title)" placeholder="Maximum 60 characters" />
                    <x-admin.form.textarea name="meta_description" label="Meta Description" rows="3"
                        :value="old('meta_description', $settings->meta_description)" placeholder="Default site description" />
                    <x-admin.form.input name="meta_keywords" label="Meta Keywords"
                        :value="old('meta_keywords', $settings->meta_keywords)" placeholder="keyword1, keyword2" />
                </div>
            </div>

            {{-- SEO & ANALYTICS --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 border-b border-gray-50 pb-4">
                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">SEO &amp; Analytics</h2>
                    <p class="mt-1 text-xs font-medium text-gray-500">
                        Google Analytics 4 loads only after a visitor accepts Analytics cookies. Leave blank to disable.
                    </p>
                </div>
                <div class="space-y-6">
                    <x-admin.form.input name="ga4_measurement_id" label="GA4 Measurement ID"
                        :value="old('ga4_measurement_id', $settings->ga4_measurement_id)" placeholder="G-XXXXXXXXXX" />
                    <x-admin.form.input name="gsc_verification" label="Google Search Console Verification"
                        :value="old('gsc_verification', $settings->gsc_verification)"
                        placeholder="Verification token (content value of the google-site-verification meta tag)" />
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-6 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                    Save Settings
                </button>
            </div>
        </form>

    </div>

@endsection
