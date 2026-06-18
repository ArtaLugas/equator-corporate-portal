@extends('admin.layouts.app')

@section('title', 'Trash News')
@section('page-title', 'Trash News')

@section('content')

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Trash News</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Manage deleted articles and restore them if needed.</p>
        </div>
        <a href="{{ route('admin.news.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Back to News
        </a>
    </div>

    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Title</x-admin.th>
            <x-admin.th>Category</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Deleted At</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($news as $item)
                <tr>
                    <x-admin.td>
                        <div>
                            <p class="font-semibold text-equator-text">{{ $item->title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $item->slug }}</p>
                        </div>
                    </x-admin.td>
                    <x-admin.td>{{ $item->category?->name ?: '—' }}</x-admin.td>
                    <x-admin.td><x-admin.status-badge :status="$item->status" :dot="true" /></x-admin.td>
                    <x-admin.td>{{ $item->deleted_at?->format('d M Y • H:i') }}</x-admin.td>
                    <x-admin.td>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.news.restore', $item->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-600">Restore</button>
                            </form>
                            <x-admin.confirm-delete :action="route('admin.news.force-delete', $item->id)" title="Permanent Delete"
                                message="This will permanently delete the article and its tag links. This cannot be undone." />
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

    <div class="mt-6">{{ $news->links() }}</div>

@endsection
