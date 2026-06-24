@extends('admin.layouts.app')

@section('title', 'Credentials — Trash')
@section('page-title', 'Credentials — Trash')

@section('content')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Trash</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Soft-deleted credentials. Restore or delete permanently.
            </p>
        </div>
        <a href="{{ route('admin.company-credentials.index') }}"
            class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">&larr;
            Back</a>
    </div>

    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th class="whitespace-normal">Title</x-admin.th>
            <x-admin.th class="w-px whitespace-nowrap">Category</x-admin.th>
            <x-admin.th class="w-px whitespace-nowrap">Deleted</x-admin.th>
            <x-admin.th class="w-px whitespace-nowrap">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($credentials as $item)
                <tr class="transition-colors hover:bg-gray-50/50">
                    <x-admin.td class="whitespace-normal">
                        <p class="font-bold text-gray-900">{{ $item->title }}</p>
                    </x-admin.td>
                    <x-admin.td>
                        <span
                            class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $item->categoryLabel() }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <span class="text-xs font-medium text-gray-500">{{ $item->deleted_at?->format('d M Y H:i') }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.company-credentials.restore', $item->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('admin.company-credentials.force-delete', $item->id) }}"
                                onsubmit="return confirm('Permanently delete this credential? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700">Delete
                                    Forever</button>
                            </form>
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <h3 class="text-sm font-extrabold text-gray-900">Trash is empty</h3>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
    </x-admin.table>

    @if ($credentials->hasPages())
        <div class="mt-6">{{ $credentials->links() }}</div>
    @endif

@endsection
