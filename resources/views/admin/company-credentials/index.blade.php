@extends('admin.layouts.app')

@section('title', 'Company Credentials')
@section('page-title', 'Company Credentials')

@php
    $badge = [
        'active' => 'bg-emerald-50 text-emerald-700',
        'expiring_soon' => 'bg-amber-50 text-amber-700',
        'expired' => 'bg-red-50 text-red-700',
        'inactive' => 'bg-gray-100 text-gray-500',
    ];
@endphp

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Company Credentials</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Certifications, licenses and accreditations.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('admin.company-credentials.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Create Credential
            </a>
            <a href="{{ route('admin.company-credentials.trash') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">
                Trash
            </a>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.company-credentials.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row md:flex-wrap">

            <div class="relative w-full flex-1 md:min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search title, issuer or number..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>

            <div class="relative w-full md:w-44">
                <select name="category"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                            {{ __('credentials.categories.' . $cat) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative w-full md:w-36">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="relative w-full md:w-40">
                <select name="sort"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="" {{ request('sort') === '' ? 'selected' : '' }}>Display Order</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                    <option value="title_desc" {{ request('sort') === 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                </select>
            </div>

            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright md:w-auto">
                Apply
            </button>
        </form>
    </div>

    {{-- BULK + TABLE --}}
    <form method="POST" action="{{ route('admin.company-credentials.bulk-destroy') }}"
        x-data="{ selected: [] }"
        @submit="if (!selected.length) $event.preventDefault()">
        @csrf

        <x-admin.bulk-trash-bar noun="credential" />

        <x-admin.table>
            <x-admin.table-head>
                <x-admin.th class="w-px"><input type="checkbox"
                        @change="selected = $event.target.checked ? [...$root.querySelectorAll('[data-row-check]')].map(c => c.value) : []"
                        class="rounded border-gray-300"></x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap">Image</x-admin.th>
                <x-admin.th class="whitespace-normal">Title</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap hidden xl:table-cell">Category</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap hidden xl:table-cell">Issuer</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap">Validity</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap hidden xl:table-cell">Translation</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap">Status</x-admin.th>
                <x-admin.th class="w-px whitespace-nowrap">Action</x-admin.th>
            </x-admin.table-head>

            <x-admin.table-body>
                @forelse($credentials as $item)
                    @php $ds = $item->displayStatus(); @endphp
                    <tr class="group transition-colors hover:bg-gray-50/50">

                        <x-admin.td>
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" data-row-check
                                x-model="selected" class="rounded border-gray-300">
                        </x-admin.td>

                        <x-admin.td>
                            @if ($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}"
                                    class="h-12 w-16 rounded-xl border border-gray-200 bg-gray-50 object-cover">
                            @else
                                <div
                                    class="flex h-12 w-16 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-gray-400">
                                    <x-icon :name="config('credentials.categories.' . $item->category . '.icon', 'file-badge')"
                                        class="h-5 w-5" />
                                </div>
                            @endif
                        </x-admin.td>

                        <x-admin.td class="whitespace-normal">
                            <p class="font-bold text-gray-900">
                                {{ $item->title }}
                                @if ($item->featured)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                        viewBox="0 0 24 24" fill="currentColor" class="ml-1 inline text-amber-400">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                @endif
                            </p>
                            @if ($item->credential_number)
                                <p class="mt-0.5 text-[11px] font-medium text-gray-400">{{ $item->credential_number }}</p>
                            @endif
                            @if ($item->items_count)
                                <p class="mt-0.5 text-[11px] font-semibold text-gray-400">{{ $item->items_count }}
                                    item(s)</p>
                            @endif
                        </x-admin.td>

                        <x-admin.td class="hidden xl:table-cell">
                            <span
                                class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $item->categoryLabel() }}</span>
                        </x-admin.td>

                        <x-admin.td class="hidden xl:table-cell">
                            <span class="text-xs font-medium text-gray-600">{{ $item->issuer ?: '—' }}</span>
                        </x-admin.td>

                        <x-admin.td>
                            <div class="text-[11px] font-medium text-gray-500">
                                <div>{{ $item->issue_date?->format('d M Y') ?: '—' }}</div>
                                @if ($item->expiry_date)
                                    <div class="text-gray-400">→ {{ $item->expiry_date->format('d M Y') }}</div>
                                @endif
                            </div>
                        </x-admin.td>

                        <x-admin.td class="hidden xl:table-cell">
                            <x-admin.translation-status :model="$item" />
                        </x-admin.td>

                        <x-admin.td>
                            <span
                                class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold {{ $badge[$ds] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ __('credentials.status.' . $ds) }}
                            </span>
                        </x-admin.td>

                        <x-admin.td>
                            <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                <a href="{{ route('admin.company-credentials.show', $item) }}"
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
                                <a href="{{ route('admin.company-credentials.edit', $item) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white"
                                    title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                    </svg>
                                </a>
                                <x-admin.confirm-delete :action="route('admin.company-credentials.destroy', $item)"
                                    title="Delete Credential"
                                    message="Move '{{ $item->title }}' to trash?" />
                            </div>
                        </x-admin.td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <h3 class="text-sm font-extrabold text-gray-900">No credentials found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">Create your first credential, or adjust
                                your filters.</p>
                        </td>
                    </tr>
                @endforelse
            </x-admin.table-body>
        </x-admin.table>
    </form>

    @if ($credentials->hasPages())
        <div class="mt-6">{{ $credentials->links() }}</div>
    @endif

@endsection
