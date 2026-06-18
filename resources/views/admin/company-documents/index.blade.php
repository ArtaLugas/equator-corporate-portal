@extends('admin.layouts.app')

@section('title', 'Company Documents')

@section('page-title', 'Company Documents')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">

                Company Documents

            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">

                Manage company profile, brochures, capability statements and other downloadable files.

            </p>

        </div>

        <div class="flex flex-col gap-2 sm:flex-row">

            <a href="{{ route('admin.company-documents.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright">

                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />

                </svg>

                Upload Document

            </a>

            {{-- Tombol Trash --}}
            <a href="{{ route('admin.company-documents.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">

                Trash

            </a>

        </div>

    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.company-documents.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">

            {{-- SEARCH --}}
            <div class="relative w-full flex-1">

                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">

                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />

                    </svg>

                </div>

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search document title or type..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text">

            </div>

            <div class="hidden h-8 w-px bg-gray-200 md:block"></div>

            {{-- STATUS --}}
            <div class="relative w-full md:w-40">

                <select name="status"
                    class="block w-full appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium">

                    <option value="">
                        All Status
                    </option>

                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>

                </select>

            </div>

            {{-- SORT --}}
            <div class="relative w-full md:w-48 lg:w-56">

                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>
                        Newest
                    </option>

                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>
                        Oldest
                    </option>

                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>
                        Title (A-Z)
                    </option>

                    <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>
                        Title (Z-A)
                    </option>

                    <option value="display_order" {{ request('sort') == 'display_order' ? 'selected' : '' }}>
                        Display Order
                    </option>

                </select>

            </div>

            <button type="submit" class="rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white">

                Apply

            </button>

        </form>

    </div>

    {{-- TABLE --}}
    <x-admin.table>

        <x-admin.table-head>

            <x-admin.th>
                Thumbnail
            </x-admin.th>

            <x-admin.th>
                Document
            </x-admin.th>

            <x-admin.th>
                Type
            </x-admin.th>

            <x-admin.th>
                Size
            </x-admin.th>

            <x-admin.th>
                Downloads
            </x-admin.th>

            <x-admin.th>
                Status
            </x-admin.th>

            <x-admin.th>
                Order
            </x-admin.th>

            <x-admin.th>
                Action
            </x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($documents as $document)
                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- THUMBNAIL --}}
                    <x-admin.td>

                        @if ($document->thumbnail)
                            <img src="{{ asset('storage/' . $document->thumbnail) }}"
                                class="h-14 w-14 rounded-xl border object-cover">
                        @else
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-xl border bg-gray-50 text-xs font-bold text-gray-400">

                                PDF

                            </div>
                        @endif

                    </x-admin.td>

                    {{-- TITLE --}}
                    <x-admin.td>

                        <div class="min-w-0">

                            <p class="truncate font-bold text-gray-900">

                                {{ $document->title }}

                            </p>

                            <p class="mt-0.5 text-xs font-medium text-gray-500">

                                {{ $document->slug }}

                            </p>

                        </div>

                    </x-admin.td>

                    {{-- TYPE --}}
                    <x-admin.td>

                        <span
                            class="inline-flex rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-bold text-gray-700">

                            {{ strtoupper($document->document_type ?? 'FILE') }}

                        </span>

                    </x-admin.td>

                    {{-- FILE SIZE --}}
                    <x-admin.td>

                        {{ $document->file_size ? number_format($document->file_size / 1024 / 1024, 2) . ' MB' : '-' }}

                    </x-admin.td>

                    {{-- DOWNLOADS --}}
                    <x-admin.td>

                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700">

                            {{ number_format($document->download_count) }}

                        </span>

                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td>

                        <x-admin.status-badge :dot="true" :status="$document->status" />

                    </x-admin.td>

                    {{-- ORDER --}}
                    <x-admin.td>
                        {{ $document->display_order }}
                    </x-admin.td>

                    <x-admin.td>
                        <div class="flex items-center gap-1 whitespace-nowrap">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.company-documents.show', $document) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-bright text-white transition"
                                title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </a>

                            {{-- EDIT --}}
                            <a href="{{ route('admin.company-documents.edit', $document) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit Banner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>

                            {{-- DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.company-documents.destroy', $document)" title="Delete Document"
                                message="Are you sure you want to delete this document? This action cannot be undone." />

                        </div>
                    </x-admin.td>

                </tr>

            @empty
                <tr>
                    <td colspan="8" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            {{-- Ikon Empty State (Gaya Flat Premium dengan Border Halus) --}}
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-file-icon lucide-file">
                                    <path
                                        d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
                                    <path d="M14 2v5a1 1 0 0 0 1 1h5" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No documents found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any documents yet, or no documents match your current search
                                criteria.
                            </p>

                        </div>
                    </td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    <div class="mt-6">

        {{ $documents->links() }}

    </div>

@endsection
