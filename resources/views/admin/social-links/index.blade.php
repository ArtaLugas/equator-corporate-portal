@extends('admin.layouts.app')

@section('title', 'Social Links')
@section('page-title', 'Social Links')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Social Links</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage social media links shown on the public site.</p>
        </div>

        <a href="{{ route('admin.social-links.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Social Link
        </a>
    </div>

    {{-- SEARCH + FILTER --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.social-links.index') }}" class="flex flex-col items-center gap-3 md:flex-row">
            <div class="relative w-full flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search platform or URL..."
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
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
            <x-admin.th>Order</x-admin.th>
            <x-admin.th>Platform</x-admin.th>
            <x-admin.th>URL</x-admin.th>
            <x-admin.th>Icon Class</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($socialLinks as $link)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    <x-admin.td>
                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-gray-100 px-2 text-xs font-bold text-gray-600">{{ $link->display_order }}</span>
                    </x-admin.td>
                    <x-admin.td><p class="font-bold text-gray-900">{{ $link->platform }}</p></x-admin.td>
                    <x-admin.td>
                        <a href="{{ $link->url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-equator-dark hover:underline">
                            {{ \Illuminate\Support\Str::limit($link->url, 40) }}
                        </a>
                    </x-admin.td>
                    <x-admin.td><code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ $link->icon_class ?: '—' }}</code></x-admin.td>
                    <x-admin.td><x-admin.status-badge :status="$link->status" :dot="true" /></x-admin.td>
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.social-links.edit', $link) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                            </a>
                            <x-admin.confirm-delete :action="route('admin.social-links.destroy', $link)" title="Delete Social Link"
                                message="Delete this social link? This action cannot be undone." />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <path d="M18 8a3 3 0 1 0-2.83-4" /><circle cx="6" cy="12" r="3" /><path d="M18 19a3 3 0 1 0 0-6" /><path d="m8.7 10.7 6.6-3.4" /><path d="m8.7 13.3 6.6 3.4" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900">No social links found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">Add your first social media link.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($socialLinks->hasPages())
        <div class="mt-6">{{ $socialLinks->links() }}</div>
    @endif

@endsection
