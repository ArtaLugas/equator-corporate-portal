@extends('layouts.public')

@section('title', $article->meta_title ?: $article->title)
@section('meta_description', $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($article->excerpt), 150))

@section('content')

    <article class="bg-white">
        {{-- HEADER --}}
        <div class="bg-equator-bg py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <nav class="mb-4 flex items-center gap-2 text-xs font-medium text-gray-400">
                    <a href="{{ route('home') }}" class="hover:text-equator-dark">Home</a><span>/</span>
                    <a href="{{ route('news.index') }}" class="hover:text-equator-dark">News</a>
                </nav>
                <div class="mb-4 flex items-center gap-3 text-sm">
                    @if ($article->category)
                        <a href="{{ route('news.index', ['category' => $article->category->slug]) }}"
                            class="rounded-full bg-equator-dark px-3 py-1 text-xs font-bold text-white">{{ $article->category->name }}</a>
                    @endif
                    <span class="text-gray-500"><i class="bi bi-calendar3 mr-1"></i>{{ $article->published_at?->format('d M Y') }}</span>
                    <span class="text-gray-500"><i class="bi bi-eye mr-1"></i>{{ number_format($article->views_count) }} views</span>
                </div>
                <h1 class="text-3xl font-black leading-tight tracking-tight text-equator-text sm:text-4xl">{{ $article->title }}</h1>
            </div>
        </div>

        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            @if ($article->image)
                <div class="mb-8 overflow-hidden rounded-2xl">
                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" class="w-full object-cover">
                </div>
            @endif

            @if ($article->excerpt)
                <p class="mb-8 text-lg font-medium leading-relaxed text-gray-600">{{ $article->excerpt }}</p>
            @endif

            <div class="prose max-w-none prose-headings:text-equator-text prose-a:text-equator-bright prose-img:rounded-xl">
                {!! $article->content !!}
            </div>

            @if ($article->tags->isNotEmpty())
                <div class="mt-10 flex flex-wrap gap-2 border-t border-gray-100 pt-6">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('news.index', ['tag' => $tag->slug]) }}"
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-equator-dark hover:text-white">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RECENT --}}
        @if ($recent->isNotEmpty())
            <div class="border-t border-gray-100 bg-equator-bg py-14">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="mb-8 text-2xl font-black text-equator-text">More Articles</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($recent as $r)
                            @include('public.partials.news-card', ['article' => $r])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </article>

@endsection
