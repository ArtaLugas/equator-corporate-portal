@extends('admin.layouts.app')

@section('title', $document->title)

@section('page-title', 'Document Details')

@section('content')

    <div class="mx-auto max-w-7xl">

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>

                <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">

                    {{ $document->title }}

                </h1>

                <p class="mt-1 text-sm font-medium text-gray-500">

                    Company document details and file information.

                </p>

            </div>

            <div class="flex flex-col gap-2 sm:flex-row">

                {{-- EDIT --}}
                <a href="{{ route('admin.company-documents.edit', $document) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-equator-orange px-5 py-3 text-sm font-bold text-white transition hover:opacity-90">

                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">

                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />

                    </svg>

                    Edit

                </a>

                {{-- BACK --}}
                <a href="{{ route('admin.company-documents.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">

                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">

                        <path d="m15 18-6-6 6-6" />

                    </svg>

                    Back

                </a>

            </div>

        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- LEFT COLUMN --}}
            <div class="lg:col-span-1">

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                    <h2 class="mb-4 text-lg font-extrabold text-equator-text">

                        Thumbnail

                    </h2>

                    @if ($document->thumbnail)
                        <img src="{{ asset('storage/' . $document->thumbnail) }}" alt="{{ $document->title }}"
                            class="w-full rounded-xl border object-cover">
                    @else
                        <div
                            class="flex h-64 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50">

                            <span class="text-sm font-semibold text-gray-400">

                                No Thumbnail

                            </span>

                        </div>
                    @endif

                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- DOCUMENT INFO --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                    <h2 class="mb-5 text-lg font-extrabold text-equator-text">

                        Document Information

                    </h2>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Title

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->title }}

                            </p>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Slug

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->slug }}

                            </p>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Document Type

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->document_type ?? '-' }}

                            </p>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Status

                            </p>

                            <div class="mt-2">

                                <x-admin.status-badge :dot="true" :status="$document->status" />

                            </div>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Display Order

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->display_order }}

                            </p>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Downloads

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ number_format($document->download_count) }}

                            </p>

                        </div>

                    </div>

                </div>

                {{-- DESCRIPTION --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                    <h2 class="mb-5 text-lg font-extrabold text-equator-text">

                        Description

                    </h2>

                    <div class="prose max-w-none">

                        {!! $document->description ?: '<p class="text-gray-400">No description available.</p>' !!}

                    </div>

                </div>

                {{-- FILE INFORMATION --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                    <h2 class="mb-5 text-lg font-extrabold text-equator-text">

                        File Information

                    </h2>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                File Size

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->file_size ? number_format($document->file_size / 1024 / 1024, 2) . ' MB' : '-' }}

                            </p>

                        </div>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">

                                Last Updated

                            </p>

                            <p class="mt-1 text-sm font-semibold text-equator-text">

                                {{ $document->updated_at?->format('d M Y H:i') }}

                            </p>

                        </div>

                    </div>

                    @if ($document->file)
                        <div class="mt-6 flex flex-wrap gap-3">

                            <a href="{{ asset('storage/' . $document->file) }}" target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-equator-dark px-5 py-3 text-sm font-bold text-white hover:bg-equator-bright">

                                Open PDF

                            </a>

                            <a href="{{ asset('storage/' . $document->file) }}" download
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">

                                Download File

                            </a>

                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>

@endsection
