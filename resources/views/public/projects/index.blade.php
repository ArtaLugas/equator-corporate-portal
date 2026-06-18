@extends('layouts.public')

@section('title', 'Projects — ' . app_setting('company_name', 'Equator Group'))

@section('content')

    @include('public.partials.page-hero', [
        'title' => 'Our Projects',
        'subtitle' => 'A track record of impactful social and environmental work across Indonesia and beyond.',
    ])

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- FILTER --}}
            <form method="GET" class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects..."
                    class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/20">
                <select name="status" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-equator-bright focus:outline-none">
                    <option value="">All Status</option>
                    @foreach (['planned' => 'Planned', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $k => $v)
                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                @if ($countries->isNotEmpty())
                    <select name="country" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-equator-bright focus:outline-none">
                        <option value="">All Countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c }}" {{ request('country') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white transition hover:bg-equator-bright">Filter</button>
            </form>

            @if ($projects->isEmpty())
                <p class="py-16 text-center text-gray-400">No projects found.</p>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        @include('public.partials.project-card', ['project' => $project])
                    @endforeach
                </div>
                <div class="mt-10">{{ $projects->links() }}</div>
            @endif
        </div>
    </section>

@endsection
