@extends('admin.layouts.app')

@section('title', 'Service Categories')

@section('page-title', 'Service Categories')

@section('content')

    {{-- HEADER HALAMAN --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        {{-- Teks Judul & Deskripsi --}}
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Service Categories
            </h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Manage and organize all service portfolios for the public site.
            </p>
        </div>

        {{-- GROUP BUTTONS --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- Tombol Create --}}
            <a href="{{ route('admin.service-categories.create') }}"
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

    {{-- SEARCH & FILTER BAR (Flat Enterprise UI - No Shadows) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.service-categories.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">

            {{-- SEARCH INPUT GROUP (Flex-1 agar memakan sisa ruang yang ada) --}}
            <div class="relative w-full flex-1">
                {{-- Search Icon --}}
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search categories by name or slug..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                {{-- Clear Button "X" --}}
                @if (request('search'))
                    <a href="{{ route('admin.service-categories.index') }}"
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

            {{-- VERTICAL DIVIDER (Khusus Desktop, menambah kesan Toolbar Premium) --}}
            <div class="hidden h-8 w-px bg-gray-200 md:block"></div>

            {{-- SORT FILTER (Ukuran Fix di Desktop agar konsisten) --}}
            <div class="relative w-full md:w-48 lg:w-56">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="display_order" {{ request('sort') == 'display_order' ? 'selected' : '' }}>Display Order
                    </option>
                </select>

                {{-- Custom Dropdown Chevron Icon (Lebih Elegan dari panah bawaan browser) --}}
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

            <x-admin.th>
                Image
            </x-admin.th>

            <x-admin.th>
                Name
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

            @forelse($categories as $category)
                <tr>

                    <x-admin.td>

                        @if ($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                class="h-14 w-14 rounded-xl border object-cover">
                        @endif

                    </x-admin.td>

                    <x-admin.td>

                        <div>

                            <p class="font-semibold text-gray-900">

                                {{ $category->name }}

                            </p>

                            <p class="mt-1 text-xs text-gray-500">

                                {{ $category->slug }}

                            </p>

                        </div>

                    </x-admin.td>

                    <x-admin.td>

                        <x-admin.status-badge :dot="true" :status="$category->status" />

                    </x-admin.td>

                    <x-admin.td>

                        {{ $category->display_order }}

                    </x-admin.td>

                    <x-admin.td>

                        <div class="flex items-center gap-2 whitespace-nowrap">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.service-categories.show', $category) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-bright text-white transition"
                                title="View">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696
                                                                                                                                                                                        10.75 10.75 0 0 1 19.876 0
                                                                                                                                                                                        1 1 0 0 1 0 .696
                                                                                                                                                                                        10.75 10.75 0 0 1-19.876 0" />

                                    <circle cx="12" cy="12" r="3" />

                                </svg>

                            </a>

                            {{-- EDIT --}}
                            <a href="{{ route('admin.service-categories.edit', $category) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">

                                    <path d="M12 20h9" />

                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />

                                </svg>

                            </a>

                            <x-admin.confirm-delete :action="route('admin.service-categories.destroy', $category)" title="Delete Category"
                                message="This action cannot be undone." />

                        </div>

                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            {{-- Ikon Empty State (Gaya Flat Premium dengan Border Halus) --}}
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No categories found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any service categories yet, or no categories match your current search
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

        {{ $categories->links() }}

    </div>

@endsection
