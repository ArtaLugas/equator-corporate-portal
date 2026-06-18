@extends('admin.layouts.app')

@section('title', 'Team')
@section('page-title', 'Team')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Team
            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">
                Manage your company team members displayed on the public site.
            </p>
        </div>

        {{-- GROUP BUTTONS --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- CREATE --}}
            <a href="{{ route('admin.teams.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">

                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>

                Create Member

            </a>

            {{-- TRASH --}}
            <a href="{{ route('admin.teams.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">

                Trash

            </a>

        </div>

    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.teams.index') }}" class="flex flex-col items-center gap-3 md:flex-row">

            {{-- SEARCH --}}
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
                    placeholder="Search members by name, position or email..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">

                @if (request('search'))
                    <a href="{{ route('admin.teams.index') }}"
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

            {{-- STATUS FILTER --}}
            <div class="relative w-full md:w-40 lg:w-44">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>

            <div class="hidden h-8 w-px bg-gray-200 md:block"></div>

            {{-- SORT --}}
            <div class="relative w-full md:w-48 lg:w-56">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="display_order" {{ request('sort') == 'display_order' ? 'selected' : '' }}>Display Order
                    </option>
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

            {{-- ACTION --}}
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
            <x-admin.th>Photo</x-admin.th>
            <x-admin.th>Name</x-admin.th>
            <x-admin.th>Position</x-admin.th>
            <x-admin.th>Order</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($teams as $team)
                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- PHOTO --}}
                    <x-admin.td>
                        @if ($team->photo)
                            <img src="{{ asset('storage/' . $team->photo) }}"
                                class="h-12 w-12 rounded-full border border-gray-200 bg-gray-50 object-cover">
                        @else
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-gray-50 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                        @endif
                    </x-admin.td>

                    {{-- NAME --}}
                    <x-admin.td>
                        <div>
                            <p class="font-bold text-gray-900">
                                {{ $team->name }}
                            </p>
                            @if ($team->email)
                                <p class="mt-0.5 text-xs font-medium text-gray-500">
                                    {{ $team->email }}
                                </p>
                            @endif
                        </div>
                    </x-admin.td>

                    {{-- POSITION --}}
                    <x-admin.td>
                        <span
                            class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                            {{ $team->position }}
                        </span>
                    </x-admin.td>

                    {{-- ORDER --}}
                    <x-admin.td>
                        <span class="text-sm font-bold text-gray-700">
                            {{ $team->display_order }}
                        </span>
                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td>
                        <x-admin.status-badge :dot="true" :status="$team->status" />
                    </x-admin.td>

                    {{-- ACTIONS --}}
                    <x-admin.td>
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.teams.show', $team) }}"
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
                            <a href="{{ route('admin.teams.edit', $team) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit Member">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>

                            {{-- DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.teams.destroy', $team)" title="Delete Member"
                                message="Are you sure you want to delete '{{ $team->name }}'? This action cannot be undone." />

                        </div>
                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">

                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>

                            <h3 class="text-sm font-extrabold text-gray-900">
                                No team members found
                            </h3>

                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't added any team members yet, or none match your current search criteria.
                            </p>

                        </div>
                    </td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    @if ($teams->hasPages())
        <div class="mt-6">
            {{ $teams->links() }}
        </div>
    @endif

@endsection
