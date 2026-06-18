@extends('admin.layouts.app')

@section('title', 'Hero Banners')
@section('page-title', 'Hero Banners')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Company Documents
            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Manage company profile, brochures, capability statements and other downloadable files.
            </p>

        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            <a href="{{ route('admin.company-documents.trash') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">

                Trash

            </a>

            <a href="{{ route('admin.company-documents.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright">

                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">

                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />

                </svg>

                Upload Document

            </a>

        </div>

    </div>

    {{-- OPTIMIZATION NOTICE (Info Box Enterprise Style) --}}
    <div class="mb-6 flex items-start gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 sm:items-center">

        {{-- Ikon Info --}}
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 16v-4" />
                <path d="M12 8h.01" />
            </svg>
        </div>

        {{-- Teks Pesan --}}
        <div>
            <p class="text-sm font-extrabold text-indigo-900">
                Performance Recommendation
            </p>
            <p class="mt-0.5 text-sm font-medium leading-relaxed text-indigo-700/90">
                For optimal website loading speed and best user experience, it is highly recommended to keep the number of
                active hero banners to a maximum of <span
                    class="rounded bg-indigo-200/50 px-1.5 py-0.5 font-bold text-indigo-900">3 items</span>.
            </p>
        </div>

    </div>

    {{-- SEARCH & FILTER BAR (Flat Enterprise UI) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.hero-banners.index') }}"
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
                    placeholder="Search banner title or subtitle..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                @if (request('search'))
                    <a href="{{ route('admin.hero-banners.index') }}"
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
                    <option value="display_order" {{ request('sort', 'latest') == 'display_order' ? 'selected' : '' }}>
                        Display Order
                    </option>

                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>
                        Newest
                    </option>

                    <option value="oldest" {{ request('sort', 'latest') == 'oldest' ? 'selected' : '' }}>
                        Oldest
                    </option>
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
            <x-admin.th>Banner</x-admin.th>
            <x-admin.th>Call to Action</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Order</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($banners as $banner)

                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- IMAGE + TITLE --}}
                    <x-admin.td>
                        <div class="flex items-center gap-4">
                            {{-- Aspect Ratio 16:9 untuk Hero Banner --}}
                            <div class="h-14 w-24 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                @if ($banner->image)
                                    <img src="{{ asset('storage/' . $banner->image) }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full flex-col items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2"
                                                ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-bold text-gray-900">
                                    {{ $banner->title ?: '(Untitled Banner)' }}
                                </p>
                                <p class="mt-0.5 max-w-[200px] truncate text-xs font-medium text-gray-500">
                                    {{ $banner->subtitle ?: 'No subtitle provided.' }}
                                </p>
                            </div>
                        </div>
                    </x-admin.td>

                    {{-- CTA BUTTON (Mockup Style) --}}
                    <x-admin.td>
                        @if ($banner->button_text)
                            <div class="flex flex-col items-start">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-bold text-gray-700 shadow-sm">
                                    {{ $banner->button_text }}
                                </span>
                                @if ($banner->button_link)
                                    <span class="mt-1.5 max-w-[150px] truncate text-[10px] font-semibold text-gray-400"
                                        title="{{ $banner->button_link }}">
                                        {{ $banner->button_link }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs font-medium italic text-gray-400">
                                No CTA Button
                            </span>
                        @endif
                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td>
                        <x-admin.status-badge :dot="true" :status="$banner->status" />
                    </x-admin.td>

                    {{-- ORDER --}}
                    <x-admin.td>
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600">
                            {{ $banner->display_order }}
                        </span>
                    </x-admin.td>

                    {{-- ACTION (Flat Enterprise Style) --}}
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.hero-banners.show', $banner) }}"
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
                            <a href="{{ route('admin.hero-banners.edit', $banner) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit Banner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>

                            {{-- DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.hero-banners.destroy', $banner)" title="Delete Banner"
                                message="Are you sure you want to delete this banner? This action cannot be undone." />

                        </div>
                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            {{-- Ikon Empty State (Image/Slider Indicator) --}}
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No hero banners found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any banners yet, or none match your search criteria. Banners added here
                                will appear on the homepage slider.
                            </p>

                        </div>
                    </td>
                </tr>

            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    @if ($banners->hasPages())
        <div class="mt-6">
            {{ $banners->links() }}
        </div>
    @endif

@endsection
