@extends('admin.layouts.app')

@section('title', 'About Histories')

@section('page-title', 'About Histories')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex items-center justify-between">

        <div>

            <h1 class="text-2xl font-extrabold text-equator-text">
                About Histories
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage company timeline and milestone history.
            </p>

        </div>

        <a href="{{ route('admin.about-histories.create') }}"
            class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-bold text-white transition hover:bg-equator-bright">

            Create History

        </a>

    </div>

    {{-- SEARCH & FILTER --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.about-histories.index') }}"
            class="flex flex-col items-center gap-3 lg:flex-row">

            {{-- SEARCH --}}
            <div class="relative w-full flex-1">

                {{-- ICON --}}
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">

                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />

                    </svg>

                </div>

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search history by title or year..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                {{-- CLEAR --}}
                @if (request('search'))
                    <a href="{{ route('admin.about-histories.index') }}"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-colors hover:text-red-500">

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

            {{-- DIVIDER --}}
            <div class="hidden h-8 w-px bg-gray-200 lg:block"></div>

            {{-- STATUS --}}
            <div class="relative w-full lg:w-44">

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

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">

                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                        <path d="m6 9 6 6 6-6" />

                    </svg>

                </div>

            </div>

            {{-- SORT --}}
            <div class="relative w-full lg:w-56">

                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>
                        Newest
                    </option>

                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>
                        Oldest
                    </option>

                    <option value="year_desc" {{ request('sort') == 'year_desc' ? 'selected' : '' }}>
                        Year Desc
                    </option>

                    <option value="year_asc" {{ request('sort') == 'year_asc' ? 'selected' : '' }}>
                        Year Asc
                    </option>

                    <option value="display_order" {{ request('sort') == 'display_order' ? 'selected' : '' }}>
                        Display Order
                    </option>

                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">

                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                        <path d="m6 9 6 6 6-6" />

                    </svg>

                </div>

            </div>

            {{-- APPLY --}}
            <div class="w-full lg:w-auto">

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright lg:w-auto">

                    Apply

                </button>

            </div>

        </form>

    </div>

    {{-- TABLE --}}
    <x-admin.table>

        <x-admin.table-head>

            <x-admin.th>Image</x-admin.th>

            <x-admin.th>Year</x-admin.th>

            <x-admin.th>Title</x-admin.th>

            <x-admin.th>Status</x-admin.th>

            <x-admin.th>Order</x-admin.th>

            <x-admin.th>Action</x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($histories as $history)
                <tr>

                    {{-- IMAGE --}}
                    <x-admin.td>

                        @if ($history->image)
                            <img src="{{ asset('storage/' . $history->image) }}" alt="{{ $history->title }}"
                                class="h-14 w-14 rounded-xl border object-cover">
                        @endif

                    </x-admin.td>

                    {{-- YEAR --}}
                    <x-admin.td>

                        <span class="font-bold text-equator-dark">

                            {{ $history->year }}

                        </span>

                    </x-admin.td>

                    {{-- TITLE --}}
                    <x-admin.td>

                        <div>

                            <p class="font-semibold text-gray-900">

                                {{ $history->title }}

                            </p>

                        </div>

                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td>

                        <x-admin.status-badge :dot="true" :status="$history->status" />

                    </x-admin.td>

                    {{-- ORDER --}}
                    <x-admin.td>

                        {{ $history->display_order }}

                    </x-admin.td>

                    {{-- ACTION --}}
                    <x-admin.td>

                        <div class="flex items-center gap-2">

                            {{-- SHOW --}}
                            <a href="{{ route('admin.about-histories.show', $history) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-equator-bright text-white transition hover:opacity-90"
                                title="View">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">

                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696
                                                                10.75 10.75 0 0 1 19.876 0
                                                                1 1 0 0 1 0 .696
                                                                10.75 10.75 0 0 1-19.876 0" />

                                    <circle cx="12" cy="12" r="3" />

                                </svg>

                            </a>

                            {{-- EDIT --}}
                            <a href="{{ route('admin.about-histories.edit', $history) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-equator-orange text-white transition hover:opacity-90"
                                title="Edit">

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                                    <path d="M12 20h9" />

                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />

                                </svg>

                            </a>

                            {{-- DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.about-histories.destroy', $history)" title="Delete History"
                                message="This action cannot be undone." />

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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-history-icon lucide-history">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                    <path d="M3 3v5h5" />
                                    <path d="M12 7v5l4 2" />
                                </svg>
                            </div>

                            {{-- Teks Utama --}}
                            <h3 class="text-sm font-extrabold text-gray-900">
                                No about histories found
                            </h3>

                            {{-- Teks Sub/Deskripsi --}}
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any about histories yet, or none match your search criteria. About
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

    {{-- PAGINATION --}}
    <div class="mt-6">

        {{ $histories->links() }}

    </div>

@endsection
