@extends('admin.layouts.app')

@section('title', 'Trash Projects')

@section('page-title', 'Trash Projects')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Trash Projects</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage deleted projects and restore them if needed.</p>
        </div>

        <a href="{{ route('admin.projects.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Back to Projects
        </a>

    </div>

    {{-- TABLE --}}
    <x-admin.table>

        <x-admin.table-head>
            <x-admin.th>Project</x-admin.th>
            <x-admin.th>Client</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Deleted At</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($projects as $project)
                <tr>

                    <x-admin.td>
                        <div>
                            <p class="font-semibold text-equator-text">{{ $project->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $project->slug }}</p>
                        </div>
                    </x-admin.td>

                    <x-admin.td>{{ $project->client_name ?: '—' }}</x-admin.td>

                    <x-admin.td>
                        <x-admin.status-badge :status="$project->status" :dot="true" />
                    </x-admin.td>

                    <x-admin.td>{{ $project->deleted_at?->format('d M Y • H:i') }}</x-admin.td>

                    <x-admin.td>
                        <div class="flex items-center gap-2">

                            {{-- RESTORE --}}
                            <form action="{{ route('admin.projects.restore', $project->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-600">
                                    Restore
                                </button>
                            </form>

                            {{-- FORCE DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.projects.force-delete', $project->id)" title="Permanent Delete"
                                message="This will permanently delete the project and all its gallery images. This cannot be undone." />

                        </div>
                    </x-admin.td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Trash is empty.</td>
                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    <div class="mt-6">
        {{ $projects->links() }}
    </div>

@endsection
