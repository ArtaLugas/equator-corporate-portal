@extends('admin.layouts.app')

@section('title', 'Trash Services')

@section('page-title', 'Trash Services')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">

                Trash Services

            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">

                Manage deleted services and restore them if needed.

            </p>

        </div>

        {{-- BACK --}}
        <a href="{{ route('admin.services.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">

            Back to Services

        </a>

    </div>

    {{-- TABLE --}}
    <x-admin.table>

        <x-admin.table-head>

            <x-admin.th>
                Service
            </x-admin.th>

            <x-admin.th>
                Category
            </x-admin.th>

            <x-admin.th>
                Deleted At
            </x-admin.th>

            <x-admin.th>
                Action
            </x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($services as $service)
                <tr>

                    {{-- SERVICE --}}
                    <x-admin.td>

                        <div>

                            <p class="font-semibold text-equator-text">

                                {{ $service->name }}

                            </p>

                            <p class="mt-1 text-xs text-gray-500">

                                {{ $service->slug }}

                            </p>

                        </div>

                    </x-admin.td>

                    {{-- CATEGORY --}}
                    <x-admin.td>

                        {{ $service->category?->name }}

                    </x-admin.td>

                    {{-- DELETED --}}
                    <x-admin.td>

                        {{ $service->deleted_at?->format('d M Y • H:i') }}

                    </x-admin.td>

                    {{-- ACTION --}}
                    <x-admin.td>

                        <div class="flex items-center gap-2">

                            {{-- RESTORE --}}
                            <form action="{{ route('admin.services.restore', $service->id) }}" method="POST">

                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-600">

                                    Restore

                                </button>

                            </form>

                            {{-- FORCE DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.services.force-delete', $service->id)" title="Permanent Delete"
                                message="This action cannot be undone forever." />

                        </div>

                    </x-admin.td>

                </tr>

            @empty

                <tr>

                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">

                        Trash is empty.

                    </td>

                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

    {{-- PAGINATION --}}
    <div class="mt-6">

        {{ $services->links() }}

    </div>

@endsection
