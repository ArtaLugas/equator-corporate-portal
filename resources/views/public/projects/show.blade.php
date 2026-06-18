@extends('layouts.public')

@section('title', $project->meta_title ?: $project->name)
@section('meta_description', $project->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($project->short_description), 150))

@section('content')

    @include('public.partials.page-hero', ['title' => $project->name, 'subtitle' => $project->client_name])

    <section class="bg-white py-16" x-data="{ lightbox: null }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">

                {{-- MAIN --}}
                <div class="lg:col-span-2">
                    @if ($project->featured_image)
                        <div class="mb-8 overflow-hidden rounded-2xl">
                            <img src="{{ asset('storage/' . $project->featured_image) }}" alt="{{ $project->name }}" class="w-full object-cover">
                        </div>
                    @endif

                    @if ($project->short_description)
                        <p class="mb-6 border-l-4 border-equator-bright bg-equator-bg py-3 pl-4 text-lg font-medium text-gray-700">{{ $project->short_description }}</p>
                    @endif

                    <div class="prose max-w-none prose-headings:text-equator-text prose-a:text-equator-bright">
                        {!! $project->description ?: '<p class="text-gray-400">No description available.</p>' !!}
                    </div>

                    {{-- GALLERY --}}
                    @if ($project->images->isNotEmpty())
                        <h2 class="mb-4 mt-12 text-xl font-black text-equator-text">Project Gallery</h2>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                            @foreach ($project->images as $img)
                                <button type="button" @click="lightbox = '{{ asset('storage/' . $img->image) }}'"
                                    class="group aspect-[16/10] overflow-hidden rounded-xl bg-gray-100">
                                    <img src="{{ asset('storage/' . $img->image) }}" alt="{{ $img->caption }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                </button>
                            @endforeach
                        </div>
                    @endif

                </div>

                {{-- SIDEBAR --}}
                <aside class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-equator-bg p-6">
                        <h3 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-equator-dark">Project Details</h3>
                        <dl class="space-y-4 text-sm">
                            @if ($project->services->isNotEmpty())
                                <div><dt class="text-gray-400">Service Scope</dt><dd class="font-bold text-equator-text">{{ $project->services->pluck('name')->implode(', ') }}</dd></div>
                            @endif
                            @if ($project->client_name)
                                <div><dt class="text-gray-400">Client</dt><dd class="font-bold text-equator-text">{{ $project->client_name }}</dd></div>
                            @endif
                            <div><dt class="text-gray-400">Location</dt><dd class="font-bold text-equator-text">{{ $project->location ?: '—' }}@if ($project->country), {{ $project->country }}@endif</dd></div>
                            <div><dt class="text-gray-400">Status</dt><dd class="font-bold capitalize text-equator-text">{{ $project->status }}</dd></div>
                            @if ($project->start_date)
                                <div><dt class="text-gray-400">Period</dt><dd class="font-bold text-equator-text">{{ $project->start_date?->format('M Y') }} — {{ $project->end_date?->format('M Y') ?: 'Present' }}</dd></div>
                            @endif
                        </dl>
                    </div>
                </aside>
            </div>

            <div class="mt-12">
                <a href="{{ route('projects.index') }}" class="text-sm font-bold text-equator-dark hover:text-equator-bright">← Back to all projects</a>
            </div>
        </div>

        {{-- LIGHTBOX --}}
        <div x-show="lightbox" x-cloak @click="lightbox = null" @keydown.escape.window="lightbox = null"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4">
            <img :src="lightbox" class="max-h-[90vh] max-w-full rounded-xl object-contain">
        </div>
    </section>

@endsection
