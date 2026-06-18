@extends('layouts.public')

@section('title', 'News & Insights — ' . app_setting('company_name', 'Equator Group'))

@section('content')

    @include('public.partials.page-hero', [
        'title' => 'News & Insights',
        'subtitle' => 'Updates, perspectives, and stories from our work in social and environmental sustainability.',
    ])

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-4">

                {{-- ARTICLES --}}
                <div class="lg:col-span-3">
                    @if ($activeCategory || $activeTag || request('search'))
                        <p class="mb-6 text-sm text-gray-500">
                            Showing results
                            @if ($activeCategory) in <strong>{{ $activeCategory->name }}</strong> @endif
                            @if ($activeTag) tagged <strong>#{{ $activeTag->name }}</strong> @endif
                            @if (request('search')) for "<strong>{{ request('search') }}</strong>" @endif
                            · <a href="{{ route('news.index') }}" class="text-equator-bright">clear</a>
                        </p>
                    @endif

                    @if ($news->isEmpty())
                        <p class="py-16 text-center text-gray-400">No articles found.</p>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            @foreach ($news as $article)
                                @include('public.partials.news-card', ['article' => $article])
                            @endforeach
                        </div>
                        <div class="mt-10">{{ $news->links() }}</div>
                    @endif
                </div>

                {{-- SIDEBAR --}}
                <aside class="space-y-8">
                    <form method="GET" action="{{ route('news.index') }}">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search news..."
                                class="w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-3 text-sm focus:border-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/20">
                            <i class="bi bi-search absolute left-3.5 top-3 text-gray-400"></i>
                        </div>
                    </form>

                    <div>
                        <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wider text-equator-dark">Categories</h3>
                        <ul class="space-y-2">
                            @foreach ($categories as $cat)
                                <li>
                                    <a href="{{ route('news.index', ['category' => $cat->slug]) }}"
                                        class="{{ $activeCategory && $activeCategory->id === $cat->id ? 'text-equator-bright' : 'text-gray-600 hover:text-equator-bright' }} flex items-center justify-between text-sm font-medium">
                                        {{ $cat->name }}
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">{{ $cat->news_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if ($popularTags->isNotEmpty())
                        <div>
                            <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wider text-equator-dark">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($popularTags as $tag)
                                    <a href="{{ route('news.index', ['tag' => $tag->slug]) }}"
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 transition hover:bg-equator-dark hover:text-white">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>

@endsection
