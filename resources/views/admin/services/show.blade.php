@extends('admin.layouts.app')

@section('title', 'Service Details')
@section('page-title', 'Service Details')

@section('content')

    {{-- ===================================================== --}}
    {{-- PAGE HEADER (Flat Enterprise Style) --}}
    {{-- ===================================================== --}}
    <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">
                    {{ $service->name }}
                </h1>

                {{-- FEATURED BADGE (Flat Style) --}}
                @if ($service->is_featured)
                    <div
                        class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-widest text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                            fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <span>Featured</span>
                    </div>
                @endif
            </div>

            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Reviewing comprehensive details and metadata for this service.
            </p>
        </div>

        {{-- ACTIONS --}}
        <div class="flex w-full items-center gap-3 sm:w-auto">

            {{-- BACK BUTTON --}}
            <a href="{{ route('admin.services.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back
            </a>

            {{-- EDIT BUTTON --}}
            <a href="{{ route('admin.services.edit', $service) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-dark focus:ring-offset-2 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                </svg>
                Edit Service
            </a>

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- MAIN GRID --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- ===================================================== --}}
        {{-- LEFT CONTENT (2 Columns Wide) --}}
        {{-- ===================================================== --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- SERVICE INFORMATION CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        Service Information
                    </h2>
                </div>

                {{-- IMAGE --}}
                @if ($service->image)
                    <div class="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                        <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}"
                            class="max-h-[400px] w-full object-cover">
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    {{-- NAME --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Service Name
                        </p>
                        <p class="mt-1.5 text-sm font-bold text-gray-900">
                            {{ $service->name }}
                        </p>
                    </div>

                    {{-- SLUG --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            URL Slug
                        </p>
                        <div
                            class="mt-1.5 inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-600">
                            <span class="text-gray-400">/</span>
                            {{ $service->slug }}
                        </div>
                    </div>
                </div>

                {{-- SEPARATOR UNTUK AREA KONTEN TEKS --}}
                <div class="my-8 border-t border-gray-100"></div>

                <div class="space-y-8">

                    {{-- SHORT DESCRIPTION (EXCERPT) --}}
                    <div>
                        <div class="mb-2.5 flex items-center gap-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                                Short Description
                            </p>
                            {{-- Context Badge --}}
                            <span
                                class="rounded border border-blue-100 bg-blue-50 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-widest text-blue-600">
                                Card Preview
                            </span>
                        </div>

                        @if ($service->short_description)
                            {{-- Tampilan Excerpt Block yang elegan --}}
                            <div class="rounded-r-xl border-l-4 border-equator-dark bg-gray-50 py-3 pl-4 pr-3">
                                <p class="text-sm font-medium leading-relaxed text-gray-700">
                                    {!! $service->short_description !!}
                                </p>
                            </div>
                        @else
                            <p class="mt-1.5 text-sm font-medium italic text-gray-400">No short description provided.</p>
                        @endif
                    </div>

                    {{-- FULL DESCRIPTION (DOCUMENT BODY) --}}
                    <div>
                        <div class="mb-3 flex items-center gap-2">
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                                Full Content Body
                            </p>
                            {{-- Context Badge --}}
                            <span
                                class="rounded border border-emerald-100 bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-widest text-emerald-600">
                                Main Page
                            </span>
                        </div>

                        @if ($service->description)
                            {{-- Tampilan Dokumen Kanvas --}}
                            <div
                                class="prose prose-sm max-w-none rounded-xl border border-gray-200 bg-white p-5 prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-equator-dark prose-img:rounded-xl md:p-7">
                                {!! $service->description !!}
                            </div>
                        @else
                            <div
                                class="flex h-32 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
                                <p class="text-sm font-medium italic text-gray-400">No comprehensive content available.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- SEO INFORMATION CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        SEO Metadata
                    </h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <path d="M2 12h4l2-9 5 18 2-9h5" />
                    </svg>
                </div>

                <div class="space-y-6">

                    {{-- META TITLE --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Meta Title
                        </p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">
                            {{ $service->meta_title ?: '—' }}
                        </p>
                    </div>

                    {{-- META DESCRIPTION --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Meta Description
                        </p>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-gray-600">
                            {{ $service->meta_description ?: '—' }}
                        </p>
                    </div>

                    {{-- META KEYWORDS --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Meta Keywords
                        </p>
                        @if ($service->meta_keywords)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (explode(',', $service->meta_keywords) as $keyword)
                                    <span
                                        class="inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                        {{ trim($keyword) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1.5 text-sm font-medium text-gray-900">—</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- RIGHT SIDEBAR (1 Column Wide) --}}
        {{-- ===================================================== --}}
        <div class="space-y-6">

            {{-- PUBLISHING CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        Publishing
                    </h2>
                </div>

                <div class="space-y-6">

                    {{-- CATEGORY --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Category
                        </p>
                        <div class="mt-2">
                            @if ($service->category)
                                <a href="{{ route('admin.service-categories.show', $service->category) }}"
                                    class="group inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 transition-colors hover:border-equator-dark hover:bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="text-gray-400 group-hover:text-equator-dark">
                                        <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                    </svg>
                                    <span class="text-sm font-bold text-gray-700 group-hover:text-equator-dark">
                                        {{ $service->category->name }}
                                    </span>
                                </a>
                            @else
                                <span class="text-sm font-medium italic text-gray-400">Uncategorized</span>
                            @endif
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Visibility Status
                        </p>
                        <div class="mt-2">
                            <x-admin.status-badge :status="$service->status" :dot="true" />
                        </div>
                    </div>

                    {{-- FEATURED TOGGLE STATE --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Promotional State
                        </p>
                        <div class="mt-2">
                            @if ($service->is_featured)
                                <div
                                    class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="currentColor" class="text-amber-500">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                    <span class="text-sm font-bold text-amber-700">Highlighted on Homepage</span>
                                </div>
                            @else
                                <div
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-600">Standard Listing</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- SYSTEM INFORMATION CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        System Information
                    </h2>
                </div>

                <div class="space-y-6">

                    {{-- CREATED --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Created At
                        </p>
                        <div class="mt-1.5 flex items-center gap-2 text-sm font-medium text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-400">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ $service->created_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                    {{-- UPDATED --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                            Last Updated
                        </p>
                        <div class="mt-1.5 flex items-center gap-2 text-sm font-medium text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ $service->updated_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
