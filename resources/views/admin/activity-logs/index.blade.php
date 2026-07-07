@extends('admin.layouts.app')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Activity Log</h1>
        <p class="mt-1.5 text-sm font-medium text-gray-500">Audit trail of administrator actions across the CMS.</p>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}"
            class="flex flex-col items-stretch gap-3 lg:flex-row lg:flex-wrap lg:items-center">

            <div class="relative w-full flex-1 lg:min-w-[200px]">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>

            {{-- MODULE --}}
            <div class="relative w-full lg:w-44">
                <select name="module"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </div>
            </div>

            {{-- ADMIN --}}
            <div class="relative w-full lg:w-44">
                <select name="admin"
                    class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Admins</option>
                    @foreach ($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </div>
            </div>

            {{-- DATE RANGE --}}
            <input type="date" name="from" value="{{ request('from') }}"
                class="w-full rounded-xl border border-transparent bg-gray-50 px-3 py-2.5 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark lg:w-40">
            <input type="date" name="to" value="{{ request('to') }}"
                class="w-full rounded-xl border border-transparent bg-gray-50 px-3 py-2.5 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark lg:w-40">

            <div class="flex w-full gap-2 lg:w-auto">
                <button type="submit"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white hover:bg-equator-bright lg:flex-none">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'module', 'admin', 'from', 'to']))
                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th class="w-36 whitespace-nowrap">Date &amp; Time</x-admin.th>
            <x-admin.th class="w-48 whitespace-nowrap">Admin</x-admin.th>
            <x-admin.th class="w-44 whitespace-nowrap">Module</x-admin.th>
            <x-admin.th>Description</x-admin.th>
            <x-admin.th class="w-40 whitespace-nowrap text-right">IP Address</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($logs as $log)
                <tr class="transition-colors hover:bg-gray-50/50">
                    <x-admin.td class="whitespace-nowrap align-top">
                        <span class="text-xs font-semibold text-gray-700">{{ $log->created_at?->format('d M Y') }}</span>
                        <span class="block text-[11px] font-medium text-gray-400">{{ $log->created_at?->format('H:i:s') }}</span>
                    </x-admin.td>
                    <x-admin.td class="whitespace-nowrap align-top">
                        <span class="text-sm font-bold text-gray-900">{{ $log->admin?->name ?? 'System' }}</span>
                    </x-admin.td>
                    <x-admin.td class="whitespace-nowrap align-top">
                        @if ($log->module)
                            <span class="inline-flex items-center rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-bold text-equator-dark">{{ $log->module }}</span>
                        @else
                            <span class="text-xs italic text-gray-400">—</span>
                        @endif
                    </x-admin.td>
                    <x-admin.td class="whitespace-normal align-top">
                        <span class="text-sm text-gray-600">{{ $log->description }}</span>
                    </x-admin.td>
                    <x-admin.td class="whitespace-nowrap text-right align-top">
                        <span class="font-mono text-xs text-gray-500">{{ $log->ip_address ?: '—' }}</span>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900">No activity found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">No log entries match your current filters.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($logs->hasPages())
        <div class="mt-6">{{ $logs->links() }}</div>
    @endif

@endsection
