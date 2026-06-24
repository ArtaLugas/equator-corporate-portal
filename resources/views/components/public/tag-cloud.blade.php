@props(['tags' => null])

@php $tags = $tags ?? collect(); @endphp

@if ($tags->isNotEmpty())
    <div class="flex flex-wrap gap-2">
        @foreach ($tags as $tag)
            <a href="{{ route('news.index', ['tag' => $tag->slug]) }}"
               class="group inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 transition-colors duration-200 hover:border-equator-dark hover:bg-equator-dark hover:text-white">
                <span class="text-slate-400 transition-colors group-hover:text-equator-orange">#</span>{{ $tag->name }}
            </a>
        @endforeach
    </div>
@else
    <p class="text-sm text-slate-400">{{ __('news.no_topics') }}</p>
@endif
