@extends('admin.layouts.app')

@section('title', 'Project Details')
@section('page-title', 'Project Details')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $project->name }}</h1>
                @if ($project->is_featured)
                    <div
                        class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-widest text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                            fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <span>Featured</span>
                    </div>
                @endif
            </div>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Reviewing comprehensive project details.</p>
        </div>

        <div class="flex w-full items-center gap-3 sm:w-auto">
            <a href="{{ route('admin.projects.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back
            </a>
            <a href="{{ route('admin.projects.edit', $project) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-dark focus:ring-offset-2 active:scale-[0.98] sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                </svg>
                Edit
            </a>
        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- OVERVIEW CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">Overview</h2>
                </div>

                @if ($project->featured_image)
                    <div class="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                        <img src="{{ asset('storage/' . $project->featured_image) }}" alt="{{ $project->name }}"
                            class="max-h-[400px] w-full object-cover">
                    </div>
                @endif

                @if ($project->short_description)
                    <div class="mb-6 rounded-r-xl border-l-4 border-equator-dark bg-gray-50 py-3 pl-4 pr-3">
                        <p class="text-sm font-medium leading-relaxed text-gray-700">{{ $project->short_description }}</p>
                    </div>
                @endif

                @if ($project->description)
                    <div
                        class="prose prose-sm max-w-none rounded-xl border border-gray-200 bg-white p-5 prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-equator-dark prose-img:rounded-xl md:p-7">
                        {!! $project->description !!}
                    </div>
                @else
                    <div
                        class="flex h-24 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
                        <p class="text-sm font-medium italic text-gray-400">No description available.</p>
                    </div>
                @endif
            </div>

            {{-- GALLERY CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8" x-data="{
                open: false,
                idx: 0,
                zoom: 1,
                images: [
                    @foreach ($project->images as $image)
                        { src: @js(asset('storage/' . $image->image)), caption: @js($image->caption) },
                    @endforeach
                ],
                show(i) { this.idx = i; this.zoom = 1; this.open = true; },
                next() { this.idx = (this.idx + 1) % this.images.length; this.zoom = 1; },
                prev() { this.idx = (this.idx - 1 + this.images.length) % this.images.length; this.zoom = 1; },
                zoomIn() { this.zoom = Math.min(this.zoom + 0.5, 4); },
                zoomOut() { this.zoom = Math.max(this.zoom - 0.5, 1); },
                get current() { return this.images[this.idx] || {}; },
            }">

                <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">Gallery</h2>
                    <span class="text-xs font-bold text-gray-400">{{ $project->images->count() }} image(s)</span>
                </div>

                @if ($project->images->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        @foreach ($project->images as $image)
                            <figure class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                <button type="button" @click="show({{ $loop->index }})"
                                    class="group relative block w-full cursor-zoom-in overflow-hidden">
                                    <img src="{{ asset('storage/' . $image->image) }}"
                                        class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105"
                                        alt="{{ $image->caption }}">
                                    {{-- Hover overlay + zoom icon --}}
                                    <span
                                        class="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition group-hover:bg-black/30 group-hover:opacity-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                            viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8" />
                                            <path d="m21 21-4.3-4.3" />
                                            <line x1="11" y1="8" x2="11" y2="14" />
                                            <line x1="8" y1="11" x2="14" y2="11" />
                                        </svg>
                                    </span>
                                </button>
                                @if ($image->caption)
                                    <figcaption class="px-2 py-1.5 text-xs font-medium text-gray-500">
                                        {{ $image->caption }}
                                    </figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm font-medium italic text-gray-400">No gallery images.</p>
                @endif

                {{-- LIGHTBOX --}}
                <div x-show="open" x-cloak @keydown.escape.window="open = false"
                    @keydown.arrow-right.window="open && next()" @keydown.arrow-left.window="open && prev()"
                    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 backdrop-blur-sm"
                    x-transition.opacity>

                    {{-- Backdrop (klik untuk tutup) --}}
                    <div class="absolute inset-0" @click="open = false"></div>

                    {{-- Top bar --}}
                    <div class="absolute inset-x-0 top-0 z-10 flex items-center justify-between px-4 py-3 sm:px-6">
                        <span class="text-xs font-semibold text-white/70"
                            x-text="(idx + 1) + ' / ' + images.length"></span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="zoomOut()" :disabled="zoom <= 1"
                                class="rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20 disabled:opacity-30"
                                aria-label="Zoom out">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                    <line x1="8" y1="11" x2="14" y2="11" />
                                </svg>
                            </button>
                            <span class="w-12 text-center text-xs font-bold text-white/80"
                                x-text="Math.round(zoom * 100) + '%'"></span>
                            <button type="button" @click="zoomIn()" :disabled="zoom >= 4"
                                class="rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20 disabled:opacity-30"
                                aria-label="Zoom in">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                    <line x1="11" y1="8" x2="11" y2="14" />
                                    <line x1="8" y1="11" x2="14" y2="11" />
                                </svg>
                            </button>
                            <button type="button" @click="open = false"
                                class="ml-2 rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20"
                                aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Prev --}}
                    <button type="button" x-show="images.length > 1" @click.stop="prev()"
                        class="absolute left-3 z-10 rounded-full bg-white/10 p-2.5 text-white transition hover:bg-white/20 sm:left-6"
                        aria-label="Previous">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                    </button>

                    {{-- Image --}}
                    <div class="relative z-[5] flex max-h-screen max-w-full items-center justify-center overflow-hidden p-4"
                        @wheel.prevent="$event.deltaY < 0 ? zoomIn() : zoomOut()">
                        <img :src="current.src" :alt="current.caption"
                            class="max-h-[82vh] max-w-[90vw] select-none rounded-lg object-contain transition-transform duration-200 ease-out"
                            :class="zoom > 1 ? 'cursor-zoom-out' : 'cursor-zoom-in'"
                            :style="'transform: scale(' + zoom + ')'"
                            @click.stop="zoom = zoom > 1 ? 1 : 2" draggable="false">
                    </div>

                    {{-- Next --}}
                    <button type="button" x-show="images.length > 1" @click.stop="next()"
                        class="absolute right-3 z-10 rounded-full bg-white/10 p-2.5 text-white transition hover:bg-white/20 sm:right-6"
                        aria-label="Next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>

                    {{-- Caption --}}
                    <p x-show="current.caption"
                        class="absolute inset-x-0 bottom-0 z-10 truncate bg-gradient-to-t from-black/70 to-transparent px-6 py-4 text-center text-sm font-medium text-white/90"
                        x-text="current.caption"></p>
                </div>
            </div>

        </div>

        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- DETAILS CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">Project Details</h2>
                </div>
                <div class="space-y-6">

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Status</p>
                        <div class="mt-2"><x-admin.status-badge :status="$project->status" :dot="true" /></div>
                    </div>

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Service Scope</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($project->services as $service)
                                <a href="{{ route('admin.services.show', $service) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-bold text-gray-700 transition-colors hover:border-equator-dark hover:text-equator-dark">
                                    {{ $service->name }}
                                </a>
                            @empty
                                <span class="text-sm font-medium italic text-gray-400">—</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Client</p>
                        <p class="mt-1.5 text-sm font-bold text-gray-900">{{ $project->client_name ?: '—' }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Location</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">
                            {{ $project->location ?: '—' }}@if ($project->country)
                                , {{ $project->country }}
                            @endif
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Start</p>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">
                                {{ $project->start_date?->format('d M Y') ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">End</p>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">
                                {{ $project->end_date?->format('d M Y') ?: '—' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">URL Slug</p>
                        <div
                            class="mt-1.5 inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-600">
                            <span class="text-gray-400">/</span>{{ $project->slug }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- SEO CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">SEO Metadata</h2>
                </div>
                <div class="space-y-5">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Meta Title</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $project->meta_title ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Meta Description</p>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-gray-600">
                            {{ $project->meta_description ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Meta Keywords</p>
                        @if ($project->meta_keywords)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (explode(',', $project->meta_keywords) as $keyword)
                                    <span
                                        class="inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">{{ trim($keyword) }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1.5 text-sm font-medium text-gray-900">—</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SYSTEM CARD --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">System Information</h2>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Created At</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">
                            {{ $project->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Last Updated</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">
                            {{ $project->updated_at?->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
