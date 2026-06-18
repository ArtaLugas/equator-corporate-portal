@extends('admin.layouts.app')

@section('title', 'About Content Details')

@section('page-title', 'About Content Details')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                About Content Details
            </h1>

            <p class="mt-1 text-sm font-medium text-gray-500">
                Complete information and preview of selected about content.
            </p>

        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3">

            {{-- BACK --}}
            <a href="{{ route('admin.about-contents.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold transition hover:bg-gray-50">

                Back

            </a>

            {{-- EDIT --}}
            <a href="{{ route('admin.about-contents.edit', $content) }}"
                class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">

                Edit Content

            </a>

        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT CONTENT --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- IMAGE --}}
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

                <div class="aspect-[16/7] bg-gray-100">

                    @if ($content->image)
                        <img src="{{ asset('storage/' . $content->image) }}" alt="{{ $content->title }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm font-semibold text-gray-400">

                            No Image Available

                        </div>
                    @endif

                </div>

            </div>

            {{-- CONTENT --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

                <div class="mb-6 border-b border-gray-50 pb-4">

                    <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                        Content Information
                    </h2>

                    <p class="mt-1 text-xs font-medium text-gray-500">
                        Main content information displayed on About page.
                    </p>

                </div>

                <div class="space-y-6">

                    {{-- TITLE --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Title
                        </p>

                        <h3 class="text-2xl font-extrabold leading-tight text-equator-text">

                            {{ $content->title ?: '—' }}

                        </h3>

                    </div>

                    {{-- CONTENT --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Content
                        </p>

                        @if ($content->content)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">

                                <div class="prose prose-sm max-w-none text-gray-700">
                                    {!! $content->content !!}
                                </div>

                            </div>
                        @else
                            <p class="text-sm font-medium text-gray-400">
                                No content available.
                            </p>
                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-6">

            {{-- CONTENT SETTINGS --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                <div class="mb-5 border-b border-gray-50 pb-4">

                    <h2 class="text-base font-extrabold tracking-tight text-equator-text">
                        Content Settings
                    </h2>

                </div>

                <div class="space-y-5">

                    {{-- SECTION --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Section
                        </p>

                        <div
                            class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-equator-text">

                            {{ $content->section?->name ?? '—' }}

                        </div>

                    </div>

                    {{-- STATUS --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Status
                        </p>

                        <x-admin.status-badge :dot="true" :status="$content->status" />

                    </div>

                    {{-- DISPLAY ORDER --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Display Order
                        </p>

                        <div
                            class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-bold text-equator-text">

                            {{ $content->display_order }}

                        </div>

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

                            {{ $content->created_at?->format('d M Y • H:i') }}

                        </p>

                    </div>

                    {{-- UPDATED --}}
                    <div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Last Updated
                        </p>

                        <p class="text-sm font-semibold text-equator-text">

                            {{ $content->updated_at?->format('d M Y • H:i') }}

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
