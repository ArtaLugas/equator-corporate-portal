@extends('admin.layouts.app')

@section('title', 'Message Details')
@section('page-title', 'Message Details')

@php
    $statusVariant = [
        'unread' => 'warning', 'read' => 'secondary', 'replied' => 'success',
        'archived' => 'info', 'spam' => 'destructive',
    ][$message->status] ?? 'secondary';

    $defaultSubject = \Illuminate\Support\Str::startsWith(strtolower($message->subject ?? ''), 're:')
        ? $message->subject
        : 'Re: ' . $message->subject;
@endphp

@section('content')

    <div x-data="{ replyOpen: false }">

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $message->subject }}</h1>
                    <x-admin.status-badge :status="$message->status" :variant="$statusVariant" :dot="true" />
                </div>
                <p class="mt-1.5 text-sm font-medium text-gray-500">
                    Received {{ $message->created_at?->format('d M Y, H:i') }}
                </p>
            </div>

            <a href="{{ route('admin.messages.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-50 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
                Back to Inbox
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- LEFT --}}
            <div class="space-y-6 xl:col-span-2">

                {{-- MESSAGE CARD --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-base font-extrabold tracking-tight text-gray-900">Message</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Name</p>
                            <p class="mt-1.5 text-sm font-bold text-gray-900">{{ $message->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Email</p>
                            <a href="mailto:{{ $message->email }}" class="mt-1.5 block text-sm font-medium text-equator-dark hover:underline">{{ $message->email }}</a>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Phone</p>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $message->phone ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Company</p>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $message->company ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="my-6 border-t border-gray-100"></div>

                    <div>
                        <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Subject</p>
                        <p class="text-sm font-bold text-gray-900">{{ $message->subject }}</p>
                    </div>

                    <div class="mt-5 rounded-r-xl border-l-4 border-equator-dark bg-gray-50 py-4 pl-5 pr-4">
                        <p class="whitespace-pre-line text-sm font-medium leading-relaxed text-gray-700">{{ $message->message }}</p>
                    </div>
                </div>

                {{-- LEAD INFORMATION (mini-CRM, auto-captured) --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-base font-extrabold tracking-tight text-gray-900">Lead Information</h2>
                        <p class="mt-1 text-xs font-medium text-gray-400">Automatically captured — the visitor did not enter this.</p>
                    </div>

                    @php
                        $leadFields = [
                            'Landing Page' => $message->landing_page,
                            'Referrer' => $message->referrer,
                            'Locale' => $message->locale ? strtoupper($message->locale) : null,
                            'UTM Source' => $message->utm_source,
                            'UTM Medium' => $message->utm_medium,
                            'UTM Campaign' => $message->utm_campaign,
                            'UTM Content' => $message->utm_content,
                            'UTM Term' => $message->utm_term,
                            'Google Click ID' => $message->gclid,
                            'Facebook Click ID' => $message->fbclid,
                            'IP Address' => $message->ip_address,
                            'User Agent' => $message->user_agent,
                        ];
                        $wide = ['Landing Page', 'Referrer', 'User Agent'];
                    @endphp

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        @foreach ($leadFields as $label => $value)
                            <div @class(['sm:col-span-2' => in_array($label, $wide, true)])>
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">{{ $label }}</p>
                                <p class="mt-1.5 break-all text-sm font-medium {{ $value ? 'text-gray-900' : 'text-gray-300' }}">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- REPLIES HISTORY --}}
                @if ($message->replies->isNotEmpty())
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                        <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="text-base font-extrabold tracking-tight text-gray-900">Reply History</h2>
                            <span class="text-xs font-bold text-gray-400">{{ $message->replies->count() }} reply(ies)</span>
                        </div>
                        <div class="space-y-4">
                            @foreach ($message->replies as $reply)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="text-xs font-bold text-gray-900">{{ $reply->subject }}</p>
                                        <span class="text-[11px] font-medium text-gray-400">{{ $reply->sent_at?->format('d M Y, H:i') }}</span>
                                    </div>
                                    <p class="whitespace-pre-line text-sm font-medium leading-relaxed text-gray-600">{{ $reply->reply_message }}</p>
                                    <p class="mt-2 text-[11px] font-semibold text-gray-400">
                                        Sent by {{ $reply->admin?->name ?? 'System' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- RIGHT: ACTIONS --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-base font-extrabold tracking-tight text-gray-900">Actions</h2>
                    </div>

                    <div class="space-y-3">

                        {{-- REPLY --}}
                        @can('reply', $message)
                            <button type="button" @click="replyOpen = true"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-4 py-3 text-sm font-bold text-white transition hover:bg-equator-bright">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7" /><path d="M20 18v-2a4 4 0 0 0-4-4H4" /></svg>
                                Reply
                            </button>
                        @endcan

                        {{-- ARCHIVE / UNARCHIVE --}}
                        @can('archive', $message)
                            @if ($message->isArchived())
                                <form action="{{ route('admin.messages.unarchive', $message) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                                        Move to Inbox
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.messages.archive', $message) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="5" x="2" y="3" rx="1" /><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8" /><path d="M10 12h4" /></svg>
                                        Archive
                                    </button>
                                </form>
                            @endif
                        @endcan

                        {{-- SPAM / NOT SPAM --}}
                        @can('spam', $message)
                            @if ($message->isSpam())
                                <form action="{{ route('admin.messages.unread', $message) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                                        Not Spam (move to Inbox)
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.messages.spam', $message) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 transition hover:bg-amber-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 5H19a2 2 0 0 1 2 2v8.5" /><path d="M17 11h-.5" /><path d="M7 7h.01" /><path d="m2 2 20 20" /><path d="M8.5 19H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2" /></svg>
                                        Mark as Spam
                                    </button>
                                </form>
                            @endif
                        @endcan

                        {{-- DELETE (Super Admin) --}}
                        @can('delete', $message)
                            <div class="border-t border-gray-100 pt-3">
                                <x-admin.confirm-delete :action="route('admin.messages.destroy', $message)" title="Move to Trash"
                                    message="Move this message to trash? You can restore it later." />
                            </div>
                        @endcan

                    </div>
                </div>

                {{-- META --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h2 class="text-base font-extrabold tracking-tight text-gray-900">Details</h2>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Created Date</p>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $message->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                        @if ($message->replied_at)
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Replied At</p>
                                <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $message->replied_at->format('d M Y, H:i') }}</p>
                            </div>
                        @endif
                        @if ($message->archived_at)
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Archived At</p>
                                <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $message->archived_at->format('d M Y, H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================= REPLY MODAL ========================= --}}
        @can('reply', $message)
            <div x-show="replyOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                {{-- backdrop --}}
                <div x-show="replyOpen" x-transition.opacity @click="replyOpen = false"
                    class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

                {{-- dialog --}}
                <div x-show="replyOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">

                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-extrabold tracking-tight text-gray-900">Reply to Message</h3>
                        <button type="button" @click="replyOpen = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.messages.reply', $message) }}" method="POST" class="px-6 py-5">
                        @csrf

                        {{-- TO --}}
                        <div class="mb-4 space-y-1.5">
                            <label class="block text-xs font-bold tracking-wide text-gray-700">To</label>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600">
                                {{ $message->name }} &lt;{{ $message->email }}&gt;
                            </div>
                        </div>

                        {{-- SUBJECT --}}
                        <div class="mb-4 space-y-1.5">
                            <label for="subject" class="block text-xs font-bold tracking-wide text-gray-700">Subject <span class="text-red-500">*</span></label>
                            <input type="text" id="subject" name="subject" required
                                value="{{ old('subject', $defaultSubject) }}"
                                class="block w-full rounded-xl border @error('subject') border-red-500 @else border-gray-200 @enderror px-4 py-2.5 text-sm font-medium text-equator-text focus:border-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/20">
                            @error('subject')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- MESSAGE --}}
                        <div class="mb-5 space-y-1.5">
                            <label for="reply_message" class="block text-xs font-bold tracking-wide text-gray-700">Message <span class="text-red-500">*</span></label>
                            <textarea id="reply_message" name="reply_message" rows="7" required
                                placeholder="Write your reply..."
                                class="block w-full resize-none rounded-xl border @error('reply_message') border-red-500 @else border-gray-200 @enderror px-4 py-3 text-sm text-equator-text focus:border-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/20">{{ old('reply_message') }}</textarea>
                            @error('reply_message')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <button type="button" @click="replyOpen = false"
                                class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition hover:bg-equator-bright">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z" /><path d="M22 2 11 13" /></svg>
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Auto-open modal if validation failed on reply --}}
            @if ($errors->any())
                <div x-init="replyOpen = true"></div>
            @endif
        @endcan

    </div>

@endsection
