@extends('admin.layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles & Permissions')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Roles &amp; Permissions</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Define roles and the modules each one can access.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Role
        </a>
    </div>

    {{-- TABLE --}}
    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Role</x-admin.th>
            <x-admin.th>Permissions</x-admin.th>
            <x-admin.th>Admins</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($roles as $role)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    <x-admin.td>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">{{ $role->name }}</span>
                            @if (in_array($role->name, $protected))
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500">System</span>
                            @endif
                        </div>
                    </x-admin.td>
                    <x-admin.td>
                        @if ($role->name === 'super_admin')
                            <span class="text-xs font-bold text-equator-dark">All permissions</span>
                        @else
                            <span class="text-xs font-medium text-gray-500">{{ $role->permissions_count }} permission(s)</span>
                        @endif
                    </x-admin.td>
                    <x-admin.td>
                        <span class="text-xs font-medium text-gray-500">{{ $adminCounts[$role->name] ?? 0 }}</span>
                    </x-admin.td>
                    <x-admin.td class="text-right">
                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-equator-orange text-white" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                            </a>
                            @unless (in_array($role->name, $protected))
                                <x-admin.confirm-delete :action="route('admin.roles.destroy', $role)" title="Delete Role"
                                    message="Delete role '{{ $role->name }}'? Admins using it must be reassigned first." />
                            @endunless
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">No roles yet</h3>
                        <p class="mt-1.5 text-sm font-medium text-gray-500">Create your first role to get started.</p>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

@endsection
