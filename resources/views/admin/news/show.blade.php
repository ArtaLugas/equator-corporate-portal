@extends('admin.layouts.app')

@section('title', 'News Details')
@section('page-title', 'News Details')

@section('content')

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $news->title }}</h1>
                @if ($news->is_featured)
                    <div class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-widest text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>
                        <span>Featured</span>
                    </div>
                @endif
            </div>
            <p class="mt-1.5 text-sm font-medium text-gray-500">Reviewing article details and metadata.</p>
        </div>

        <div class="flex w-full items-center gap-3 sm:w-auto">
            <a href="{{ route('admin.news.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-50 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
                Back
            </a>
            <a href="{{ route('admin.news.edit', $news) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                Edit News
            </a>
        </div>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT --}}
        <div class="space-y-6 xl:col-span-2">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">Content</h2>
                </div>

                @if ($news->image)
                    <div class="mb-8 overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                        <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" class="max-h-[400px] w-full object-cover">
                    </div>
                @endif

                @if ($news->excerpt)
                    <div class="mb-6 rounded-r-xl border-l-4 border-equator-dark bg-gray-50 py-3 pl-4 pr-3">
                        <p class="text-sm font-medium leading-relaxed text-gray-700">{{ $news->excerpt }}</p>
                    </div>
                @endif

                @if ($news->content)
                    <div class="prose prose-sm max-w-none rounded-xl border border-gray-200 bg-white p-5 prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-equator-dark prose-img:rounded-xl md:p-7">
                        {!! $news->content !!}
                    </div>
                @else
                    <div class="flex h-24 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
                        <p class="text-sm font-medium italic text-gray-400">No content.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT --}}
        <div class="space-y-6">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">Publishing</h2>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Status</p>
                        <div class="mt-2"><x-admin.status-badge :status="$news->status" :dot="true" /></div>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Category</p>
                        <p class="mt-1.5 text-sm font-bold text-gray-900">{{ $news->category?->name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Published At</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $news->published_at?->format('d M Y, H:i') ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Views</p>
                        <p class="mt-1.5 text-sm font-bold text-gray-900">{{ number_format($news->views_count) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Tags</p>
                        @if ($news->tags->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($news->tags as $tag)
                                    <span class="rounded-lg bg-equator-dark/5 px-2.5 py-1 text-xs font-bold text-equator-dark">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1.5 text-sm font-medium text-gray-900">—</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-base font-extrabold tracking-tight text-gray-900">SEO Metadata</h2>
                </div>
                <div class="space-y-5">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Meta Title</p>
                        <p class="mt-1.5 text-sm font-medium text-gray-900">{{ $news->meta_title ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Meta Description</p>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-gray-600">{{ $news->meta_description ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Slug</p>
                        <div class="mt-1.5 inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-600">
                            <span class="text-gray-400">/</span>{{ $news->slug }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
