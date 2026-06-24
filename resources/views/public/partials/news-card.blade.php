<a href="{{ route('news.show', $article->slug) }}"
    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    <div class="aspect-[16/9] overflow-hidden bg-gray-100">
        @if ($article->image)
            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/30">
                <i class="bi bi-newspaper text-4xl"></i>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <div class="mb-3 flex items-center gap-2 text-xs">
            @if ($article->category)
                <span
                    class="rounded-full bg-equator-dark/5 px-2.5 py-0.5 font-bold text-equator-dark">{{ $article->category->name }}</span>
            @endif
            <span class="text-gray-400">{{ $article->published_at?->format('d M Y') }}</span>
        </div>
        <h3
            class="line-clamp-2 text-base font-extrabold leading-snug text-equator-text group-hover:text-equator-bright">
            {{ $article->title }}</h3>
        <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-equator-bright">{{ __('common.read_more') }}
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                class="transition group-hover:translate-x-1">
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
            </svg>
        </span>
    </div>
</a>
