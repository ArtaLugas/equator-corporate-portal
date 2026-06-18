@extends('admin.layouts.app')

@section('title', 'Team Member Details')
@section('page-title', 'Team Member Details')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">
                {{ $team->name }}
            </h1>

            <p class="mt-1.5 text-sm font-medium text-gray-500">
                {{ $team->position }}
            </p>
        </div>

        {{-- ACTIONS --}}
        <div class="flex w-full items-center gap-3 sm:w-auto">

            <a href="{{ route('admin.teams.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back
            </a>

            <a href="{{ route('admin.teams.edit', $team) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-dark focus:ring-offset-2 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                </svg>
                Edit Member
            </a>

        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT CONTENT --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- PROFILE CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        Profile Information
                    </h2>
                </div>

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                    {{-- PHOTO --}}
                    @if ($team->photo)
                        <img src="{{ asset('storage/' . $team->photo) }}" alt="{{ $team->name }}"
                            class="h-28 w-28 shrink-0 rounded-full border border-gray-200 bg-gray-50 object-cover">
                    @else
                        <div
                            class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border border-gray-100 bg-gray-50 text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Full Name</p>
                            <p class="mt-1 text-sm font-bold text-gray-900">{{ $team->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Position</p>
                            <p class="mt-1 text-sm font-medium text-gray-700">{{ $team->position }}</p>
                        </div>
                    </div>

                </div>

                <div class="my-8 border-t border-gray-100"></div>

                {{-- BIO --}}
                <div>
                    <p class="mb-2.5 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                        Biography
                    </p>
                    @if ($team->bio)
                        <p class="text-sm font-medium leading-relaxed text-gray-700">
                            {{ $team->bio }}
                        </p>
                    @else
                        <p class="text-sm font-medium italic text-gray-400">No biography provided.</p>
                    @endif
                </div>

            </div>

            {{-- CONTACT CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        Contact &amp; Social
                    </h2>
                </div>

                <div class="space-y-6">

                    {{-- EMAIL --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Email Address</p>
                        @if ($team->email)
                            <a href="mailto:{{ $team->email }}"
                                class="mt-1.5 inline-block text-sm font-medium text-equator-dark hover:underline">
                                {{ $team->email }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm font-medium text-gray-900">—</p>
                        @endif
                    </div>

                    {{-- LINKEDIN --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">LinkedIn</p>
                        @if ($team->linkedin_url)
                            <a href="{{ $team->linkedin_url }}" target="_blank" rel="noopener"
                                class="mt-1.5 inline-block break-all text-sm font-medium text-equator-dark hover:underline">
                                {{ $team->linkedin_url }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm font-medium text-gray-900">—</p>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-6">

            {{-- PUBLISHING CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        Publishing
                    </h2>
                </div>

                <div class="space-y-6">

                    {{-- STATUS --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Visibility Status</p>
                        <div class="mt-2">
                            <x-admin.status-badge :status="$team->status" :dot="true" />
                        </div>
                    </div>

                    {{-- DISPLAY ORDER --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Display Order</p>
                        <p class="mt-1.5 text-sm font-bold text-gray-900">{{ $team->display_order }}</p>
                    </div>

                </div>
            </div>

            {{-- SYSTEM INFORMATION CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">
                        System Information
                    </h2>
                </div>

                <div class="space-y-6">

                    {{-- CREATED --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Created At</p>
                        <div class="mt-1.5 flex items-center gap-2 text-sm font-medium text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-400">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ $team->created_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                    {{-- UPDATED --}}
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Last Updated</p>
                        <div class="mt-1.5 flex items-center gap-2 text-sm font-medium text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ $team->updated_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
