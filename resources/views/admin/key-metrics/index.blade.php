@extends('admin.layouts.app')

@section('title', 'Key Metrics')
@section('page-title', 'Key Metrics')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Key Metrics</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Statistics shown on the homepage stats strip.</p>
        </div>
        <a href="{{ route('admin.key-metrics.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Metric
        </a>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.key-metrics.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search label or value..."
                class="block w-full flex-1 rounded-xl border border-transparent bg-gray-50 px-4 py-2.5 text-sm font-medium text-equator-text placeholder-gray-400 hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            <div class="relative w-full md:w-40">
                <select name="status"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500"><svg
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg></div>
            </div>
            <div class="relative w-full md:w-40">
                <select name="featured"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Featured</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500"><svg
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg></div>
            </div>
            <button type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white hover:bg-equator-bright md:w-auto">Apply</button>
        </form>
    </div>

    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Order</x-admin.th>
            <x-admin.th>Icon</x-admin.th>
            <x-admin.th>Value</x-admin.th>
            <x-admin.th>Label</x-admin.th>
            <x-admin.th>Featured</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($metrics as $metric)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    <x-admin.td><span
                            class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-gray-100 px-2 text-xs font-bold text-gray-600">{{ $metric->display_order }}</span></x-admin.td>
                    <x-admin.td>
                        @if ($metric->icon)
                            <x-icon :name="$metric->icon" class="h-5 w-5 text-equator-text" />
                        @else
                            <span class="text-xs italic text-gray-400">—</span>
                        @endif
                    </x-admin.td>
                    <x-admin.td><span class="font-extrabold text-equator-dark">{{ $metric->value }}</span></x-admin.td>
                    <x-admin.td><span class="text-sm font-medium text-gray-700">{{ $metric->label }}</span></x-admin.td>
                    <x-admin.td>
                        @if ($metric->is_featured)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-equator-orange/10 px-2.5 py-1 text-xs font-bold text-equator-orange">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 7.1-1.01z" />
                                </svg>
                                Featured
                            </span>
                        @else
                            <span class="text-xs italic text-gray-400">—</span>
                        @endif
                    </x-admin.td>
                    <x-admin.td><x-admin.status-badge :status="$metric->status" :dot="true" /></x-admin.td>
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.key-metrics.edit', $metric) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white"
                                title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                            </a>
                            <x-admin.confirm-delete :action="route('admin.key-metrics.destroy', $metric)" title="Delete Metric"
                                message="Delete this metric? This action cannot be undone." />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">No metrics yet</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500">Add metrics to show on the homepage. If empty,
                            default values are used.</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($metrics->hasPages())
        <div class="mt-6">{{ $metrics->links() }}</div>
    @endif

@endsection
