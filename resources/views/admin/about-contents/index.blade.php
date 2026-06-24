@extends('admin.layouts.app')

@section('title', 'About Contents')

@section('page-title', 'About Contents')

@section('content')

    <div class="mb-8 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-extrabold text-equator-text">
                About Contents
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage all about page contents.
            </p>
        </div>

        <a href="{{ route('admin.about-contents.create') }}"
            class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-bold text-white hover:bg-equator-bright">

            Create Content

        </a>

    </div>

    <x-admin.table>

        <x-admin.table-head>

            <x-admin.th>Image</x-admin.th>
            <x-admin.th class="hidden xl:table-cell">Section</x-admin.th>
            <x-admin.th>Title</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th class="hidden xl:table-cell">Translation</x-admin.th>
            <x-admin.th class="hidden xl:table-cell">Order</x-admin.th>
            <x-admin.th>Action</x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($contents as $content)
                <tr>

                    <x-admin.td>

                        @if ($content->image)
                            <img src="{{ asset('storage/' . $content->image) }}"
                                class="h-14 w-14 rounded-xl border object-cover">
                        @endif

                    </x-admin.td>

                    <x-admin.td class="hidden xl:table-cell">

                        {{ $content->section?->name }}

                    </x-admin.td>

                    <x-admin.td>

                        {{ $content->title }}

                    </x-admin.td>

                    <x-admin.td>

                        <x-admin.status-badge :dot="true" :status="$content->status" />

                    </x-admin.td>

                    <x-admin.td class="hidden xl:table-cell">

                        <x-admin.translation-status :model="$content" />

                    </x-admin.td>

                    <x-admin.td class="hidden xl:table-cell">

                        {{ $content->display_order }}

                    </x-admin.td>

                    <x-admin.td>

                        <div class="flex items-center gap-2">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.about-contents.show', $content) }}"
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
                            <a href="{{ route('admin.about-contents.edit', $content) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition hover:opacity-90"
                                title="Edit">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path d="M12 20h9" />

                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />

                                </svg>

                            </a>

                            <x-admin.confirm-delete :action="route('admin.about-contents.destroy', $content)" title="Delete Content"
                                message="This action cannot be undone." />

                        </div>

                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            {{-- Ikon Empty State (Image/Slider Indicator) --}}
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-gray-400">
                                    <path d="M16 5H3" />
                                    <path d="M16 12H3" />
                                    <path d="M16 19H3" />
                                    <path d="M21 5h.01" />
                                    <path d="M21 12h.01" />
                                    <path d="M21 19h.01" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No about contents found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any about contents yet, or none match your search criteria. About
                                contents added here
                                will appear on the about page.
                                will appear on the homepage slider.
                            </p>

                        </div>
                    </td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    <div class="mt-6">

        {{ $contents->links() }}

    </div>

@endsection
