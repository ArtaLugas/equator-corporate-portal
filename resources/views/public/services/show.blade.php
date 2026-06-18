@extends('layouts.public')

@section('title', $service->meta_title ?: $service->name)
@section('meta_description', $service->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($service->short_description), 150))

@section('content')

    @include('public.partials.page-hero', ['title' => $service->name, 'subtitle' => $service->category?->name])

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">

                {{-- MAIN --}}
                <div class="lg:col-span-2">
                    @if ($service->image)
                        <div class="mb-8 overflow-hidden rounded-2xl">
                            <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="w-full object-cover">
                        </div>
                    @endif

                    @if ($service->short_description)
                        <p class="mb-6 border-l-4 border-equator-bright bg-equator-bg py-3 pl-4 text-lg font-medium text-gray-700">{{ $service->short_description }}</p>
                    @endif

                    <div class="prose max-w-none prose-headings:text-equator-text prose-a:text-equator-bright">
                        {!! $service->description ?: '<p class="text-gray-400">No detailed description available.</p>' !!}
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <aside class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-equator-bg p-6">
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-equator-dark">Category</h3>
                        <p class="mt-2 text-gray-600">{{ $service->category?->name ?? '—' }}</p>
                        <a href="{{ route('contact') }}" class="mt-5 block rounded-xl bg-equator-dark px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-equator-bright">Request this service</a>
                    </div>

                    @if ($related->isNotEmpty())
                        <div class="rounded-2xl border border-gray-100 p-6">
                            <h3 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-equator-dark">Related Services</h3>
                            <ul class="space-y-3">
                                @foreach ($related as $r)
                                    <li><a href="{{ route('services.show', $r->slug) }}" class="flex items-start gap-2 text-sm font-medium text-gray-600 hover:text-equator-bright"><i class="bi bi-arrow-right-short text-lg text-equator-bright"></i>{{ $r->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </aside>
            </div>

            <div class="mt-12">
                <a href="{{ route('services.index') }}" class="text-sm font-bold text-equator-dark hover:text-equator-bright">← Back to all services</a>
            </div>
        </div>
    </section>

    {{-- Record this service in the "recently viewed" rail shown on the services index --}}
    <script>
        (function () {
            const KEY = 'equator_recent_services';
            const entry = {
                name: @json($service->name),
                url: @json(route('services.show', $service->slug)),
                category: @json($service->category?->name),
            };
            try {
                let items = JSON.parse(localStorage.getItem(KEY) || '[]');
                if (!Array.isArray(items)) items = [];
                items = items.filter(it => it && it.url !== entry.url);   // de-dupe, most-recent-first
                items.unshift(entry);
                localStorage.setItem(KEY, JSON.stringify(items.slice(0, 8)));
            } catch (e) { /* storage unavailable — non-critical */ }
        })();
    </script>

@endsection
