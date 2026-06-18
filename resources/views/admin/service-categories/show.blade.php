@extends('admin.layouts.app')

@section('title', $category->name)
@section('page-title', 'Category Detail')

@section('content')

    <div class="space-y-6">

        {{-- HEADER (Title & Primary Actions) --}}
        <div
            class="flex flex-col gap-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                        {{ $category->name }}
                    </h1>
                    {{-- Status Badge Component --}}
                    <x-admin.status-badge :status="$category->status" dot />
                </div>
                <div class="mt-1.5 flex items-center gap-2 text-sm font-medium text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                    <span>{{ $category->slug }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Back Button --}}
                <a href="{{ route('admin.service-categories.index') }}"
                    class="flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-50 hover:text-equator-text focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                    Back
                </a>

                {{-- Edit Button (Enterprise Primary) --}}
                <a href="{{ route('admin.service-categories.edit', $category) }}"
                    class="flex items-center gap-2 rounded-xl bg-equator-dark px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                        <path d="m15 5 4 4" />
                    </svg>
                    Edit Category
                </a>
            </div>
        </div>

        {{-- GRID LAYOUT --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- LEFT COLUMN (Main Content) --}}
            <div class="space-y-6 xl:col-span-2">

                {{-- IMAGE BANNER (Full Width 16:10 with Alpine.js Lightbox) --}}
                @if ($category->image)
                    <div x-data="{ lightboxOpen: false }">

                        {{-- THUMBNAIL --}}
                        <div @click="lightboxOpen = true"
                            class="group relative aspect-[16/10] w-full cursor-pointer overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)] transition-all hover:shadow-[0_8px_30px_rgb(38,53,146,0.08)]">
                            <img src="{{ asset('storage/' . $category->image) }}"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                alt="{{ $category->name }}">

                            {{-- Hover Overlay (Expand Indicator) --}}
                            <div
                                class="absolute inset-0 flex items-center justify-center bg-equator-dark/0 transition-colors duration-300 group-hover:bg-equator-dark/20">
                                <div
                                    class="scale-90 transform rounded-full border border-white/20 bg-white/20 p-3.5 text-white opacity-0 shadow-xl backdrop-blur-md transition-all duration-300 group-hover:scale-100 group-hover:opacity-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 3H5a2 2 0 0 0-2 2v3" />
                                        <path d="M21 8V5a2 2 0 0 0-2-2h-3" />
                                        <path d="M3 16v3a2 2 0 0 0 2 2h3" />
                                        <path d="M16 21h3a2 2 0 0 0 2-2v-3" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Inner Shadow Overlay --}}
                            <div
                                class="pointer-events-none absolute inset-0 z-10 rounded-2xl ring-1 ring-inset ring-black/10">
                            </div>
                        </div>

                        {{-- LIGHTBOX MODAL (Fullscreen Overlay) --}}
                        <template x-teleport="body">
                            <div x-show="lightboxOpen" style="display: none;"
                                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                                @keydown.escape.window="lightboxOpen = false">

                                {{-- Backdrop Blur --}}
                                <div x-show="lightboxOpen" x-transition.opacity.duration.300ms @click="lightboxOpen = false"
                                    class="absolute inset-0 cursor-zoom-out bg-black/85 backdrop-blur-md"></div>

                                {{-- Close Button (X) --}}
                                <button @click="lightboxOpen = false"
                                    class="absolute right-6 top-6 z-50 rounded-xl border border-white/10 bg-white/10 p-2.5 text-white/70 backdrop-blur-md transition-all hover:bg-white/20 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>

                                {{-- Original Size Image --}}
                                <img x-show="lightboxOpen" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                                    src="{{ asset('storage/' . $category->image) }}"
                                    class="relative z-10 max-h-[90vh] max-w-full rounded-xl border border-white/10 object-contain shadow-[0_20px_50px_rgba(0,0,0,0.5)]"
                                    alt="{{ $category->name }}">
                            </div>
                        </template>

                    </div>
                @endif

                {{-- DESCRIPTION CARD --}}
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)] sm:p-8">
                    <div class="mb-6 flex items-center gap-3 border-b border-gray-50 pb-4">
                        <div class="rounded-lg bg-equator-dark/10 p-2 text-equator-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="21" x2="3" y1="6" y2="6" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                                <line x1="21" x2="7" y1="18" y2="18" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Service Description</h2>
                    </div>

                    {{-- Content --}}
                    @if ($category->description)
                        <div
                            class="prose prose-sm max-w-none prose-headings:font-bold prose-headings:text-equator-text prose-p:text-gray-600 prose-a:text-equator-dark prose-strong:text-equator-text prose-li:text-gray-600">
                            {!! $category->description !!}
                        </div>
                    @else
                        <div
                            class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-sm text-gray-500">
                            No description provided for this category.
                        </div>
                    @endif

                </div>

                {{-- SEO CARD --}}
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)] sm:p-8">
                    <div class="mb-6 flex items-center gap-3 border-b border-gray-50 pb-4">
                        <div class="rounded-lg bg-equator-bright/10 p-2 text-equator-bright">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-extrabold tracking-tight text-equator-text">SEO Information</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Meta Title</p>
                            <p class="text-sm font-semibold text-equator-text">{{ $category->meta_title ?: '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Meta Description
                            </p>
                            <p class="text-sm font-medium leading-relaxed text-gray-600">
                                {{ $category->meta_description ?: '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Meta Keywords</p>
                            <p class="text-sm font-medium text-gray-600">{{ $category->meta_keywords ?: '—' }}</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN (System Sidebar) --}}
            <div class="space-y-6">

                {{-- SYSTEM INFO CARD --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)]">
                    <div class="mb-5 flex items-center gap-3 border-b border-gray-50 pb-4">
                        <div class="rounded-lg bg-gray-100 p-2 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                <line x1="16" x2="16" y1="2" y2="6" />
                                <line x1="8" x2="8" y1="2" y2="6" />
                                <line x1="3" x2="21" y1="10" y2="10" />
                                <path d="M8 14h.01" />
                                <path d="M12 14h.01" />
                                <path d="M16 14h.01" />
                                <path d="M8 18h.01" />
                                <path d="M12 18h.01" />
                                <path d="M16 18h.01" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-extrabold tracking-tight text-equator-text">System Information</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Display Order</p>
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-equator-dark/10 text-xs font-bold text-equator-dark">
                                {{ $category->display_order }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-50 py-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Created At</p>
                            <p class="text-xs font-semibold text-gray-700">
                                {{ $category->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-50 py-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Last Updated</p>
                            <p class="text-xs font-semibold text-gray-700">
                                {{ $category->updated_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- You can add other cards here (e.g., Statistics on the number of projects using this category) --}}

            </div>

        </div>

    </div>

@endsection
