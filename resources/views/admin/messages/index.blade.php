@extends('admin.layouts.app')

@section('title', 'Messages')
@section('page-title', 'Messages')

@php
    $statusVariant = [
        'unread' => 'warning',
        'read' => 'secondary',
        'replied' => 'success',
        'archived' => 'info',
        'spam' => 'destructive',
    ];

    $tabs = [
        '' => 'All',
        'unread' => 'Unread',
        'read' => 'Read',
        'replied' => 'Replied',
        'archived' => 'Archived',
        'spam' => 'Spam',
    ];
@endphp

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Messages</h1>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Inbox for contact form submissions.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('admin.messages.analytics') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="m19 9-5 5-4-4-3 3" />
                </svg>
                Lead Analytics
            </a>

            @can('viewTrash', App\Models\Message::class)
                <a href="{{ route('admin.messages.trash') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    Trash
                </a>
            @endcan
        </div>
    </div>

    {{-- FILTER TABS --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach ($tabs as $key => $label)
            @php $active = (string) request('status') === (string) $key; @endphp
            <a href="{{ route('admin.messages.index', array_filter(['status' => $key, 'search' => request('search')])) }}"
                class="{{ $active ? 'bg-equator-dark text-white border-equator-dark' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold transition-colors">
                {{ $label }}
                <span
                    class="{{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }} rounded-md px-1.5 py-0.5 text-[11px] font-bold">
                    {{ $counts[$key === '' ? 'all' : $key] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- SEARCH --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-2.5">
        <form method="GET" action="{{ route('admin.messages.index') }}"
            class="flex flex-col items-center gap-3 md:flex-row">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative w-full flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name, email or subject..."
                    class="block w-full rounded-xl border border-transparent bg-gray-50 py-2.5 pl-11 pr-4 text-sm font-medium text-equator-text placeholder-gray-400 transition-colors hover:bg-gray-100 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark">
            </div>
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright md:w-auto">
                Search
            </button>
        </form>
    </div>

    {{-- TABLE --}}
    <form method="POST" action="{{ route('admin.messages.bulk-destroy') }}"
        x-data="{ selected: [] }"
        @submit="if (!selected.length) $event.preventDefault()">
        @csrf

        <x-admin.bulk-trash-bar noun="message" />

        <x-admin.table>
        <x-admin.table-head>
            <x-admin.th class="w-px"><input type="checkbox"
                    @change="selected = $event.target.checked ? [...$root.querySelectorAll('[data-row-check]')].map(c => c.value) : []"
                    class="rounded border-gray-300"></x-admin.th>
            <x-admin.th>Name</x-admin.th>
            <x-admin.th>Email</x-admin.th>
            <x-admin.th>Subject</x-admin.th>
            <x-admin.th>Status</x-admin.th>
            <x-admin.th>Date</x-admin.th>
            <x-admin.th>Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @forelse($messages as $message)
                <tr
                    class="{{ $message->isUnread() ? 'bg-equator-bright/[0.03]' : '' }} group transition-colors hover:bg-gray-50/50">
                    <x-admin.td>
                        <input type="checkbox" name="ids[]" value="{{ $message->id }}" data-row-check
                            x-model="selected" class="rounded border-gray-300">
                    </x-admin.td>
                    <x-admin.td>
                        <div class="flex items-center gap-2">
                            @if ($message->isUnread())
                                <span class="h-2 w-2 shrink-0 rounded-full bg-equator-bright" title="Unread"></span>
                            @endif
                            <span
                                class="{{ $message->isUnread() ? 'font-extrabold text-gray-900' : 'font-bold text-gray-800' }}">
                                {{ $message->name }}
                            </span>
                        </div>
                    </x-admin.td>
                    <x-admin.td>
                        <span class="text-sm text-gray-600">{{ $message->email }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <span
                            class="text-sm font-medium text-gray-700">{{ \Illuminate\Support\Str::limit($message->subject, 40) }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <x-admin.status-badge :status="$message->status" :variant="$statusVariant[$message->status] ?? 'secondary'" :dot="true" />
                    </x-admin.td>
                    <x-admin.td>
                        <span
                            class="text-xs font-medium text-gray-500">{{ $message->created_at?->format('d M Y, H:i') }}</span>
                    </x-admin.td>
                    <x-admin.td>
                        <a href="{{ route('admin.messages.show', $message) }}"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-equator-bright px-3 text-xs font-bold text-white transition hover:bg-equator-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Open
                        </a>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-16">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center text-center">
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-gray-400">
                                    <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900">No messages found</h3>
                            <p class="mt-1.5 text-sm font-medium text-gray-500">There are no messages matching this view.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-body>
        </x-admin.table>
    </form>

    @if ($messages->hasPages())
        <div class="mt-6">{{ $messages->links() }}</div>
    @endif

@endsection
