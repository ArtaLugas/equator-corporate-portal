@extends('admin.layouts.app')

@section('title', 'News Categories')
@section('page-title', 'News Categories')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">News Categories</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Organize news articles into categories.</p>
        </div>

        <a href="{{ route('admin.news-categories.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create Category
        </a>
    </div>

    {{-- SEARCH --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.news-categories.index') }}"
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
                    placeholder="Search categories by name or slug..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>
            <div class="relative w-full md:w-48">
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
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright md:w-auto">
                Apply
            </button>
        </form>
    </div>

    {{-- TABLE --}}
    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Name</x-admin.th>
            <x-admin.th>Slug</x-admin.th>
            <x-admin.th>Articles</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($categories as $category)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    <x-admin.td>
                        <p class="font-bold text-gray-900">{{ $category->name }}</p>
                    </x-admin.td>
                    <x-admin.td>
                        <span class="text-xs font-medium text-gray-500">{{ $category->slug }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <span
                            class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                            {{ $category->news_count }}
                        </span>
                    </x-admin.td>
                    <x-admin.td>
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.news-categories.edit', $category) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white transition"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>
                            <x-admin.confirm-delete :action="route('admin.news-categories.destroy', $category)" title="Delete Category"
                                message="Are you sure you want to delete '{{ $category->name }}'? This action cannot be undone." />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">No categories found</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500">Create your first news category to get started.
                        </p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($categories->hasPages())
        <div class="mt-6">{{ $categories->links() }}</div>
    @endif

@endsection
