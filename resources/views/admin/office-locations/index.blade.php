@extends('admin.layouts.app')

@section('title', 'Office Locations')
@section('page-title', 'Office Locations')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Office Locations</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Head office & branches shown on the public Contact page
                and footer.</p>
        </div>

        <a href="{{ route('admin.office-locations.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Location
        </a>
    </div>

    {{-- SEARCH + FILTER --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.office-locations.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">
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
                    placeholder="Search name, address or email..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>
            <div class="relative w-full md:w-40">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
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
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white hover:bg-equator-bright md:w-auto">
                Apply
            </button>
        </form>
    </div>

    {{-- TABLE --}}
    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Location</x-admin.th>
            <x-admin.th class="hidden md:table-cell">Contact</x-admin.th>
            <x-admin.th class="hidden md:table-cell">Status</x-admin.th>
            <x-admin.th class="text-right">Actions</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($locations as $location)
                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- LOCATION --}}
                    <x-admin.td class="w-full max-w-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-gray-100 px-1.5 text-[11px] font-bold text-gray-500">{{ $location->display_order }}</span>
                                <p class="truncate font-bold text-gray-900">{{ $location->name }}</p>
                                @if ($location->is_primary)
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-equator-orange/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-equator-orange">Primary</span>
                                @endif
                            </div>
                            <p class="mt-0.5 truncate text-xs font-medium text-gray-500">{{ $location->address ?: '—' }}</p>
                        </div>
                    </x-admin.td>

                    {{-- CONTACT --}}
                    <x-admin.td class="hidden md:table-cell">
                        <div class="flex flex-col gap-0.5 text-xs">
                            <span class="font-medium text-gray-600">{{ $location->phone ?: '—' }}</span>
                            <span class="text-gray-400">{{ $location->email ?: '—' }}</span>
                        </div>
                    </x-admin.td>

                    {{-- STATUS --}}
                    <x-admin.td class="hidden md:table-cell">
                        <x-admin.status-badge :status="$location->status" :dot="true" />
                    </x-admin.td>

                    {{-- ACTIONS --}}
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.office-locations.edit', $location) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>
                            <x-admin.confirm-delete :action="route('admin.office-locations.destroy', $location)" title="Delete Office Location"
                                message="Delete this location? This action cannot be undone." />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-gray-400">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900">No locations found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">Add your head office or a branch to show on
                                the site.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($locations->hasPages())
        <div class="mt-6">{{ $locations->links() }}</div>
    @endif

@endsection
