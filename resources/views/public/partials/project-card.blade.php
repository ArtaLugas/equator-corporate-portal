@php
    $statusColor = ['planned' => 'bg-amber-100 text-amber-700', 'ongoing' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-emerald-100 text-emerald-700'][$project->status] ?? 'bg-gray-100 text-gray-600';
@endphp
<a href="{{ route('projects.show', $project->slug) }}"
    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    <div class="aspect-[16/10] overflow-hidden bg-gray-100">
        @if ($project->featured_image)
            <img src="{{ asset('storage/' . $project->featured_image) }}" alt="{{ $project->name }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-equator-dark/10 to-equator-bright/10 text-equator-dark/30">
                <i class="bi bi-folder2-open text-4xl"></i>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <div class="mb-3 flex items-center gap-2">
            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">{{ $project->status }}</span>
            @if ($project->country)
                <span class="text-xs font-medium text-gray-400">{{ $project->country }}</span>
            @endif
        </div>
        <h3 class="line-clamp-2 text-base font-extrabold leading-snug text-equator-text group-hover:text-equator-bright">{{ $project->name }}</h3>
        @if ($project->client_name)
            <p class="mt-2 text-sm text-gray-500"><i class="bi bi-building mr-1"></i>{{ $project->client_name }}</p>
        @endif
    </div>
</a>
