@extends('admin.layouts.app')

@section('title', 'Trash Categories')

@section('page-title', 'Trash Categories')

@section('content')

    <div class="mb-8 flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-black tracking-tight">

                Trash Categories

            </h1>

            <p class="mt-2 text-gray-500">

                Restore or permanently delete categories.

            </p>

        </div>

        <a href="{{ route('admin.service-categories.index') }}"
            class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

            Back

        </a>

    </div>

    <x-admin.table>

        <x-admin.table-head>

            <x-admin.th>
                Name
            </x-admin.th>

            <x-admin.th>
                Deleted At
            </x-admin.th>

            <x-admin.th>
                Action
            </x-admin.th>

        </x-admin.table-head>

        <x-admin.table-body>

            @forelse($categories as $category)
                <tr>

                    <x-admin.td>

                        {{ $category->name }}

                    </x-admin.td>

                    <x-admin.td>

                        {{ $category->deleted_at }}

                    </x-admin.td>

                    <x-admin.td>

                        <div class="flex items-center gap-2">

                            {{-- RESTORE --}}
                            <form action="{{ route('admin.service-categories.restore', $category->id) }}" method="POST">

                                @csrf
                                @method('PATCH')

                                <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white">

                                    Restore

                                </button>

                            </form>

                            {{-- FORCE DELETE --}}
                            <x-admin.confirm-delete :action="route('admin.service-categories.force-delete', $category->id)" title="Permanent Delete"
                                message="This action cannot be undone." />

                        </div>

                    </x-admin.td>

                </tr>

            @empty

                <tr>

                    <td colspan="3" class="px-6 py-10 text-center text-gray-500">

                        Trash empty.

                    </td>

                </tr>
            @endforelse

        </x-admin.table-body>

    </x-admin.table>

@endsection
