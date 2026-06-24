@extends('admin.layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')

    {{-- HEADER HALAMAN --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        {{-- Teks Judul & Deskripsi --}}
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Services
            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Manage all detailed services offered under your categories.
            </p>
        </div>

        {{-- GROUP BUTTONS --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- Tombol Create --}}
            <a href="{{ route('admin.services.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">

                {{-- Ikon Plus --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>

                </svg>

                Create Service

            </a>

            {{-- Tombol Trash --}}
            <a href="{{ route('admin.services.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">

                Trash

            </a>

        </div>

    </div>

    {{-- SEARCH & FILTER BAR (Flat Enterprise UI) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.services.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">

            {{-- SEARCH INPUT GROUP --}}
            <div class="relative w-full flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search services by name or slug..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                @if (request('search'))
                    <a href="{{ route('admin.services.index') }}"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-colors hover:text-red-500"
                        title="Clear search">
                        <div class="rounded-md p-1 hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </div>
                    </a>
                @endif
            </div>

            <div class="hidden h-8 w-px bg-gray-200 md:block"></div>

            {{-- SORT FILTER --}}
            <div class="relative w-full md:w-48 lg:w-56">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>

            {{-- ACTION BUTTON --}}
            <div class="w-full md:w-auto">
                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-dark focus:ring-offset-2 md:w-auto">
                    Apply
                </button>
            </div>

        </form>
    </div>

    {{-- TABLE --}}
    <x-admin.table>

        <x-admin.table-head>
            <x-admin.th>Image</x-admin.th>
            <x-admin.th>Service Name</x-admin.th>
            <x-admin.th>Category</x-admin.th>
            <x-admin.th>Translation</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Featured</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($services as $service)
                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- IMAGE --}}
                    <x-admin.td>
                        @if ($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}"
                                class="h-12 w-12 rounded-xl border border-gray-200 bg-gray-50 object-cover">
                        @else
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>
                        @endif
                    </x-admin.td>

                    {{-- NAME & SLUG --}}
                    <x-admin.td>
                        <div>
                            <p class="font-bold text-gray-900">
                                {{ $service->name }}
                            </p>
                            <p class="mt-0.5 text-xs font-medium text-gray-500">
                                {{ $service->slug }}
                            </p>
                        </div>
                    </x-admin.td>

                    {{-- CATEGORY RELATIONSHIP --}}
                    <x-admin.td>
                        @if ($service->category)
                            <span
                                class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                {{ $service->category->name }}
                            </span>
                        @else
                            <span class="text-xs font-medium italic text-gray-400">Uncategorized</span>
                        @endif
                    </x-admin.td>

                    {{-- TRANSLATION STATUS --}}
                    <x-admin.td>
                        <x-admin.translation-status :model="$service" />
                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td>
                        <x-admin.status-badge :dot="true" :status="$service->status" />
                    </x-admin.td>

                    {{-- FEATURED STAR --}}
                    <x-admin.td>
                        @if ($service->is_featured)
                            <div class="flex items-center gap-1.5 text-amber-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                                <span class="text-xs font-bold">Yes</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">

                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />

                                </svg>

                                <span class="text-xs font-semibold">

                                    No

                                </span>
                            </div>
                        @endif
                    </x-admin.td>

                    {{-- ACTIONS (Flat Enterprise Style) --}}
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.services.show', $service) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-bright text-white transition"
                                title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </a>

                            {{-- EDIT --}}
                            <a href="{{ route('admin.services.edit', $service) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit Service">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>

                            {{-- DELETE (Using the upgraded flat component) --}}
                            <x-admin.confirm-delete :action="route('admin.services.destroy', $service)" title="Delete Service"
                                message="Are you sure you want to delete '{{ $service->name }}'? This action cannot be undone." />

                        </div>
                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            {{-- Ikon Empty State (Gaya Flat Premium dengan Border Halus) --}}
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-toolbox-icon lucide-toolbox">
                                    <path d="M16 12v4" />
                                    <path
                                        d="M16 6a2 2 0 0 1 1.414.586l4 4A2 2 0 0 1 22 12v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 .586-1.414l4-4A2 2 0 0 1 8 6z" />
                                    <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                    <path d="M2 14h20" />
                                    <path d="M8 12v4" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No services found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any services yet, or no services match your current search criteria.
                            </p>

                        </div>
                    </td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    @if ($services->hasPages())
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif

@endsection
