{{-- Ranked horizontal-bar list. Expects: $title (string), $items (['label'=>count]). --}}
@php($max = collect($items)->max() ?: 1)

<div class="rounded-2xl border border-gray-200 bg-white p-6">
    <h2 class="mb-5 text-sm font-extrabold tracking-tight text-gray-900">{{ $title }}</h2>

    @if (empty($items))
        <p class="text-sm text-gray-400">No data yet.</p>
    @else
        <div class="space-y-3">
            @foreach ($items as $label => $count)
                <div>
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <span class="truncate text-sm font-medium text-gray-700" title="{{ $label }}">{{ $label }}</span>
                        <span class="shrink-0 text-sm font-bold text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-equator-bright" style="width: {{ max(4, round($count / $max * 100)) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
