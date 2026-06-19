@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')

    <div class="mx-auto max-w-3xl">

        {{-- HEADER --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Notifications</h1>
                <p class="mt-1.5 text-sm font-medium text-gray-500">All your in-app notifications.</p>
            </div>

            <div class="flex items-center gap-2">
                @if (auth('admin')->user()->unreadNotifications()->count() > 0)
                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">
                            Mark all read
                        </button>
                    </form>
                @endif

                @if ($notifications->total() > 0)
                    <form action="{{ route('admin.notifications.clear') }}" method="POST"
                        onsubmit="return confirm('Delete all notifications?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-100">
                            Clear all
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- LIST --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            @forelse ($notifications as $notif)
                <a href="{{ route('admin.notifications.read', $notif->id) }}" rel="nofollow"
                    class="{{ is_null($notif->read_at) ? 'bg-equator-bright/[0.04]' : '' }} flex items-start gap-4 border-b border-gray-50 px-5 py-4 transition-colors hover:bg-gray-50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-equator-dark/5 text-equator-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" /><rect x="2" y="4" width="20" height="16" rx="2" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $notif->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-gray-600">{{ $notif->data['body'] ?? '' }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-400">{{ $notif->created_at?->diffForHumans() }}</p>
                    </div>
                    @if (is_null($notif->read_at))
                        <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-equator-bright/10 px-2 py-0.5 text-[10px] font-bold text-equator-bright">
                            <span class="h-1.5 w-1.5 rounded-full bg-equator-bright"></span> New
                        </span>
                    @endif
                </a>
            @empty
                <div class="px-6 py-20 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50/50 text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-extrabold text-gray-900">No notifications</h3>
                    <p class="mt-1.5 text-sm font-medium text-gray-500">You're all caught up.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif

    </div>

@endsection
