@extends('admin.layouts.app')

@section('title', 'Trash Messages')
@section('page-title', 'Trash Messages')

@section('content')

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Trash Messages</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Deleted messages — restore or permanently delete.</p>
        </div>
        <a href="{{ route('admin.messages.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Back to Inbox
        </a>
    </div>

    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Name</x-admin.th>
            <x-admin.th>Email</x-admin.th>
            <x-admin.th>Subject</x-admin.th>
            <x-admin.th>Deleted At</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($messages as $message)
                <tr>
                    <x-admin.td><span class="font-semibold text-equator-text">{{ $message->name }}</span></x-admin.td>
                    <x-admin.td><span class="text-sm text-gray-600">{{ $message->email }}</span></x-admin.td>
                    <x-admin.td><span class="text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($message->subject, 40) }}</span></x-admin.td>
                    <x-admin.td>{{ $message->deleted_at?->format('d M Y • H:i') }}</x-admin.td>
                    <x-admin.td>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.messages.restore', $message->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-600">Restore</button>
                            </form>
                            <x-admin.confirm-delete :action="route('admin.messages.force-delete', $message->id)" title="Permanent Delete"
                                message="This will permanently delete the message and its replies. This cannot be undone." />
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

    <div class="mt-6">{{ $messages->links() }}</div>

@endsection
