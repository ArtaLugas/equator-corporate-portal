@extends('admin.layouts.app')

@section('title', 'Hero Banner Details')

@section('page-title', 'Hero Banner Details')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Hero Banner Details
            </h1>

            <p class="mt-1 text-sm font-medium text-gray-500">
                Complete information and preview of selected hero banner.
            </p>

        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3">

            {{-- BACK --}}
            <a href="{{ route('admin.hero-banners.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold transition hover:bg-gray-50">

                Back

            </a>

            {{-- EDIT --}}
            <a href="{{ route('admin.hero-banners.edit', $banner) }}"
                class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">

                Edit Banner

            </a>

        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT CONTENT --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- HERO IMAGE --}}
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

                <div class="aspect-[16/7] bg-gray-100">

                    @if ($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm font-semibold text-gray-400">

                            No Banner Image

                        </div>
                    @endif

                </div>

            </div>

            {{-- CONTENT --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

                <div class="mb-6 border-b border-gray-50 pb-4">

                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                        Banner Content
                    </h2>

                    <p class="mt-1 text-xs font-medium text-gray-500">
                        Main textual information displayed on homepage hero section.
                    </p>

                </div>

                <div class="space-y-6">

                    {{-- TITLE --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Banner Title
                        </p>

                        <h3 class="text-2xl font-extrabold leading-tight text-equator-text">

                            {{ $banner->title ?: '—' }}

                        </h3>

                    </div>

                    {{-- SUBTITLE --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Subtitle
                        </p>

                        @if ($banner->subtitle)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">

                                <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $banner->subtitle }}
                                </p>

                            </div>
                        @else
                            <p class="text-sm font-medium text-gray-400">
                                No subtitle available.
                            </p>
                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-6">

            {{-- STATUS --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                <div class="mb-5 border-b border-gray-50 pb-4">

                    <h2 class="text-base font-extrabold tracking-tight text-equator-text">
                        Visibility
                    </h2>

                </div>

                <div class="space-y-5">

                    {{-- STATUS --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Status
                        </p>

                        <x-admin.status-badge :dot="true" :status="$banner->status" />

                    </div>

                    {{-- DISPLAY ORDER --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Display Order
                        </p>

                        <div
                            class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-bold text-equator-text">

                            {{ $banner->display_order }}

                        </div>

                    </div>

                </div>

            </div>

            {{-- BUTTON SETTINGS --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                <div class="mb-5 border-b border-gray-50 pb-4">

                    <h2 class="text-base font-extrabold tracking-tight text-equator-text">
                        CTA Button
                    </h2>

                </div>

                <div class="space-y-5">

                    {{-- BUTTON TEXT --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Button Text
                        </p>

                        @if ($banner->button_text)
                            <div
                                class="inline-flex items-center rounded-xl bg-equator-dark px-4 py-2 text-sm font-bold text-white">

                                {{ $banner->button_text }}

                            </div>
                        @else
                            <p class="text-sm font-medium text-gray-400">
                                No button text.
                            </p>
                        @endif

                    </div>

                    {{-- BUTTON LINK --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Button Link
                        </p>

                        @if ($banner->button_link)
                            <a href="{{ $banner->button_link }}" target="_blank"
                                class="break-all text-sm font-semibold text-equator-bright hover:underline">

                                {{ $banner->button_link }}

                            </a>
                        @else
                            <p class="text-sm font-medium text-gray-400">
                                No button link.
                            </p>
                        @endif

                    </div>

                </div>

            </div>

            {{-- SYSTEM INFO --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                <div class="mb-5 border-b border-gray-50 pb-4">

                    <h2 class="text-base font-extrabold tracking-tight text-equator-text">
                        System Information
                    </h2>

                </div>

                <div class="space-y-5">

                    {{-- CREATED --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Created At
                        </p>

                        <p class="text-sm font-semibold text-equator-text">

                            {{ $banner->created_at?->format('d M Y • H:i') }}

                        </p>

                    </div>

                    {{-- UPDATED --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Last Updated
                        </p>

                        <p class="text-sm font-semibold text-equator-text">

                            {{ $banner->updated_at?->format('d M Y • H:i') }}

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
