@extends('admin.layouts.app')

@section('title', 'Projects')
@section('page-title', 'Projects')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Projects</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage portfolio projects and their galleries.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('admin.projects.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Create Project
            </a>

            <a href="{{ route('admin.projects.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">
                Trash
            </a>
        </div>

    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">

        <form method="GET" action="{{ route('admin.projects.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row md:flex-wrap">

            {{-- SEARCH --}}
            <div class="relative w-full flex-1 md:min-w-[220px]">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name, slug, client or location..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-10 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                @if (request('search'))
                    <a href="{{ route('admin.projects.index') }}"
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

            {{-- STATUS FILTER --}}
            <div class="relative w-full md:w-40">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Status</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>

            {{-- SERVICE FILTER --}}
            @if ($services->isNotEmpty())
                <div class="relative w-full md:w-44">
                    <select name="service"
                        class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                        <option value="">All Services</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" {{ request('service') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>
            @endif

            {{-- SORT --}}
            <div class="relative w-full md:w-40">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
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
            <x-admin.th>Image</x-admin.th>
            <x-admin.th>Name Project</x-admin.th>
            <x-admin.th class="hidden md:table-cell">Status</x-admin.th>
            <x-admin.th class="hidden md:table-cell">Translation</x-admin.th>
            <x-admin.th class="text-right">Actions</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($projects as $project)
                @php
                    // Info sekunder: tampilkan services bila ada; jika tidak ada, baru tampilkan lokasi.
                    $servicesText = $project->services->pluck('name')->implode(', ');
                    $metaText = $servicesText ?: ($project->location ?: $project->country);
                @endphp
                <tr class="group transition-colors hover:bg-gray-50/50">

                    {{-- IMAGE --}}
                    <x-admin.td>
                        @if ($project->featured_image)
                            <img src="{{ asset('storage/' . $project->featured_image) }}"
                                class="h-12 w-16 shrink-0 rounded-xl border border-gray-200 bg-gray-50 object-cover">
                        @else
                            <div
                                class="flex h-12 w-16 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-400">
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

                    {{-- NAME PROJECT (name + service·location, dipangkas) --}}
                    <x-admin.td class="w-full max-w-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <p class="truncate font-bold text-gray-900">{{ $project->name }}</p>

                                @if ($project->is_featured)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="currentColor" class="shrink-0 text-amber-400"
                                        title="Featured">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                @endif

                                {{-- Status badge inline untuk layar kecil (kolom Status disembunyikan di mobile) --}}
                                <span class="md:hidden">
                                    <x-admin.status-badge :dot="true" :status="$project->status" />
                                </span>
                            </div>

                            <p class="mt-0.5 truncate text-xs font-medium text-gray-500">
                                {{ $metaText ?: '—' }}
                            </p>
                        </div>
                    </x-admin.td>

                    {{-- STATUS + gallery count (disembunyikan di mobile) --}}
                    <x-admin.td class="hidden md:table-cell">
                        <div class="flex flex-col items-start gap-1.5">
                            <x-admin.status-badge :dot="true" :status="$project->status" />
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                                {{ $project->images_count }} photos
                            </span>
                        </div>
                    </x-admin.td>

                    {{-- TRANSLATION STATUS --}}
                    <x-admin.td class="hidden md:table-cell">
                        <x-admin.translation-status :model="$project" />
                    </x-admin.td>

                    {{-- ACTIONS --}}
                    <x-admin.td>
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.projects.show', $project) }}"
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

                            <a href="{{ route('admin.projects.edit', $project) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit Project">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>

                            <x-admin.confirm-delete :action="route('admin.projects.destroy', $project)" title="Delete Project"
                                message="Are you sure you want to delete '{{ $project->name }}'? It will be moved to trash." />
                        </div>
                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <path
                                        d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900">No projects found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">
                                You haven't created any projects yet, or none match your current search criteria.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    @if ($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif

@endsection
