@extends('admin.layouts.app')

@section('title', 'News')
@section('page-title', 'News')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">News</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage news articles, categories and tags.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('admin.news-categories.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                Categories
            </a>
            <a href="{{ route('admin.news.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Create News
            </a>
            <a href="{{ route('admin.news.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">
                Trash
            </a>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.news.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row md:flex-wrap">

            <div class="relative w-full flex-1 md:min-w-[200px]">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by title, slug or excerpt..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>

            {{-- CATEGORY --}}
            <div class="relative w-full md:w-40">
                <select name="category"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>

            {{-- STATUS --}}
            <div class="relative w-full md:w-36">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>

            {{-- SORT --}}
            <div class="relative w-full md:w-40">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="published" {{ request('sort') == 'published' ? 'selected' : '' }}>Publish Date</option>
                    <option value="most_viewed" {{ request('sort') == 'most_viewed' ? 'selected' : '' }}>Most Viewed
                    </option>
                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                    <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
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
            <x-admin.th>Image</x-admin.th>
            <x-admin.th>Title</x-admin.th>
            <x-admin.th>Category</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Views</x-admin.th>
            <x-admin.th>Published</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($news as $item)
                <tr class="group transition-colors hover:bg-gray-50/50">

                    <x-admin.td>
                        @if ($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}"
                                class="h-12 w-16 rounded-xl border border-gray-200 bg-gray-50 object-cover">
                        @else
                            <div
                                class="flex h-12 w-16 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>
                        @endif
                    </x-admin.td>

                    <x-admin.td>
                        <div>
                            <p class="font-bold text-gray-900">
                                {{ $item->title }}
                                @if ($item->is_featured)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                        viewBox="0 0 24 24" fill="currentColor" class="ml-1 inline text-amber-400">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                @endif
                            </p>
                            @if ($item->tags->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach ($item->tags->take(3) as $tag)
                                        <span
                                            class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500">{{ $tag->name }}</span>
                                    @endforeach
                                    @if ($item->tags->count() > 3)
                                        <span
                                            class="text-[10px] font-semibold text-gray-400">+{{ $item->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </x-admin.td>

                    <x-admin.td>
                        @if ($item->category)
                            <span
                                class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $item->category->name }}</span>
                        @else
                            <span class="text-xs italic text-gray-400">—</span>
                        @endif
                    </x-admin.td>

                    <x-admin.td>
                        <x-admin.status-badge :dot="true" :status="$item->status" />
                    </x-admin.td>

                    <x-admin.td>
                        <span class="text-sm font-bold text-gray-700">{{ number_format($item->views_count) }}</span>
                    </x-admin.td>

                    <x-admin.td>
                        <span
                            class="text-xs font-medium text-gray-500">{{ $item->published_at?->format('d M Y') ?: '—' }}</span>
                    </x-admin.td>

                    <x-admin.td>
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.news.show', $item) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-bright text-white"
                                title="View">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </a>
                            <a href="{{ route('admin.news.edit', $item) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>
                            <x-admin.confirm-delete :action="route('admin.news.destroy', $item)" title="Delete News"
                                message="Are you sure you want to delete '{{ $item->title }}'? It will be moved to trash." />
                        </div>
                    </x-admin.td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">No news found</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500">Create your first article, or adjust your
                            filters.</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($news->hasPages())
        <div class="mt-6">{{ $news->links() }}</div>
    @endif

@endsection
