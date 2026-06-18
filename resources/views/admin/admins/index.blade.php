@extends('admin.layouts.app')

@section('title', 'Admins')
@section('page-title', 'Admin Management')

@php
    $roleVariant = ['super_admin' => 'primary', 'admin' => 'secondary'];
@endphp

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Admin Management</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage administrator accounts, roles and access.</p>
        </div>
        <a href="{{ route('admin.admins.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Admin
        </a>
    </div>

    {{-- FILTER --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.admins.index') }}" class="flex flex-col items-center gap-3 md:flex-row">
            <div class="relative w-full flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>
            <div class="relative w-full md:w-40">
                <select name="role" class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </div>
            </div>
            <div class="relative w-full md:w-40">
                <select name="status" class="block w-full cursor-pointer appearance-none rounded-xl border border-transparent bg-gray-50 py-2.5 pl-4 pr-10 text-sm font-medium text-equator-text hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
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
            <x-admin.th>Admin</x-admin.th>
            <x-admin.th>Role</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Last Login</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($admins as $admin)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    <x-admin.td>
                        <div class="flex items-center gap-3">
                            @if ($admin->avatar)
                                <img src="{{ asset('storage/' . $admin->avatar) }}" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-900 text-[11px] font-bold text-white">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ $admin->name }}
                                    @if ($admin->id === auth('admin')->id())
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500">You</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </x-admin.td>
                    <x-admin.td>
                        <x-admin.status-badge :status="$admin->role" :variant="$roleVariant[$admin->role] ?? 'secondary'" />
                    </x-admin.td>
                    <x-admin.td>
                        <x-admin.status-badge :status="$admin->status" :dot="true" />
                    </x-admin.td>
                    <x-admin.td>
                        <span class="text-xs font-medium text-gray-500">{{ $admin->last_login_at?->format('d M Y, H:i') ?? 'Never' }}</span>
                    </x-admin.td>
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.admins.edit', $admin) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                            </a>
                            @if ($admin->id !== auth('admin')->id())
                                <x-admin.confirm-delete :action="route('admin.admins.destroy', $admin)" title="Delete Admin"
                                    message="Delete admin '{{ $admin->name }}'? This action cannot be undone." />
                            @endif
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">No admins found</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500">Adjust your filters or add a new admin.</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($admins->hasPages())
        <div class="mt-6">{{ $admins->links() }}</div>
    @endif

@endsection
