@extends('admin.layouts.app')

@section('title', 'Core Values')

@section('page-title', 'Core Values')

@section('content')

    {{-- HEADER HALAMAN --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        {{-- Teks Judul & Deskripsi --}}
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Core Values
            </h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Manage and organize all core values for the public site.
            </p>
        </div>

        {{-- GROUP BUTTONS --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- Tombol Create --}}
            <a href="{{ route('admin.core-values.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">

                {{-- Ikon Plus --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>

                </svg>

                Create Core Value

            </a>

        </div>

    </div>

    {{-- SEARCH & FILTER BAR (Flat Enterprise UI - No Shadows) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.core-values.index') }}"
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
                    placeholder="Search core values by title or description..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                {{-- Clear Button "X" --}}
                @if (request('search'))
                    <a href="{{ route('admin.core-values.index') }}"
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
            <div class="relative w-full md:w-40">

                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

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

            {{-- SORT FILTER (Ukuran Fix di Desktop agar konsisten) --}}
            <div class="relative w-full md:w-48 lg:w-56">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>
                        Title (A-Z)
                    </option>

                    <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>
                        Title (Z-A)
                    </option>
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
                Title
            </x-admin.th>

            <x-admin.th>
                Icon
            </x-admin.th>

            <x-admin.th>
                Status
            </x-admin.th>

            <x-admin.th>
                Display Order
            </x-admin.th>

            <x-admin.th>
                Action
            </x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($coreValues as $coreValue)
                <tr>
                    <x-admin.td>

                        <div>

                            <p class="font-semibold text-gray-900">

                                {{ $coreValue->title }}

                            </p>

                            @if ($coreValue->description)
                                <p class="mt-1 line-clamp-1 text-xs text-gray-500">

                                    {{ strip_tags($coreValue->description) }}

                                </p>
                            @endif

                        </div>

                    </x-admin.td>

                    <x-admin.td>

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50">

                                @if ($coreValue->icon)
                                    <x-icon :name="$coreValue->icon" class="h-5 w-5 text-equator-text" />
                                @endif

                            </div>

                            <div>

                                <p class="font-mono text-xs text-gray-600">

                                    {{ $coreValue->icon ?: '—' }}

                                </p>

                            </div>

                        </div>

                    </x-admin.td>

                    <x-admin.td>

                        <x-admin.status-badge :dot="true" :status="$coreValue->status" />

                    </x-admin.td>

                    <x-admin.td>

                        {{ $coreValue->display_order }}

                    </x-admin.td>

                    <x-admin.td>

                        <div class="flex items-center gap-2 whitespace-nowrap">

                            {{-- EDIT --}}
                            <a href="{{ route('admin.core-values.edit', $coreValue) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">

                                    <path d="M12 20h9" />

                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />

                                </svg>

                            </a>

                            <x-admin.confirm-delete :action="route('admin.core-values.destroy', $coreValue)" title="Delete Core Value"
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-message-square-heart-icon lucide-message-square-heart">
                                    <path
                                        d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z" />
                                    <path
                                        d="M7.5 9.5c0 .687.265 1.383.697 1.844l3.009 3.264a1.14 1.14 0 0 0 .407.314 1 1 0 0 0 .783-.004 1.14 1.14 0 0 0 .398-.31l3.008-3.264A2.77 2.77 0 0 0 16.5 9.5 2.5 2.5 0 0 0 12 8a2.5 2.5 0 0 0-4.5 1.5" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No core values found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any core values yet, or no core values match your current search
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

        {{ $coreValues->links() }}

    </div>

@endsection
