@extends('admin.layouts.app')

@section('title', $aboutHistory->title)

@section('page-title', 'History Detail')

@section('content')

    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-extrabold text-equator-text">

                    {{ $aboutHistory->title }}

                </h1>

                <p class="mt-1 text-sm text-gray-500">

                    Company timeline milestone details.

                </p>

            </div>

            <div class="flex items-center gap-3">

                <a href="{{ route('admin.about-histories.edit', $aboutHistory) }}"
                    class="rounded-xl bg-equator-orange px-5 py-3 text-sm font-bold text-white hover:opacity-90">

                    Edit

                </a>

                <a href="{{ route('admin.about-histories.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Back

                </a>

            </div>

        </div>

        {{-- BASIC INFORMATION --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

            <h2 class="mb-6 border-b border-gray-100 pb-4 text-lg font-extrabold text-equator-text">

                History Information

            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">

                        Year

                    </p>

                    <p class="mt-2 text-sm font-semibold text-gray-900">

                        {{ $aboutHistory->year }}

                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">

                        Status

                    </p>

                    <div class="mt-2">

                        <x-admin.status-badge :status="$aboutHistory->status" :dot="true" />

                    </div>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">

                        Display Order

                    </p>

                    <p class="mt-2 text-sm font-semibold text-gray-900">

                        {{ $aboutHistory->display_order }}

                    </p>

                </div>

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">

                        Created At

                    </p>

                    <p class="mt-2 text-sm font-semibold text-gray-900">

                        {{ $aboutHistory->created_at->format('d M Y H:i') }}

                    </p>

                </div>

            </div>

        </div>

        {{-- DESCRIPTION --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

            <h2 class="mb-6 border-b border-gray-100 pb-4 text-lg font-extrabold text-equator-text">

                Description

            </h2>

            <div class="prose prose-sm max-w-none">

                {!! $aboutHistory->description !!}

            </div>

        </div>

        {{-- IMAGE --}}
        @if ($aboutHistory->image)
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                <h2 class="mb-6 border-b border-gray-100 pb-4 text-lg font-extrabold text-equator-text">

                    Timeline Image

                </h2>

                <img src="{{ asset('storage/' . $aboutHistory->image) }}" alt="{{ $aboutHistory->title }}"
                    class="w-full max-w-4xl rounded-xl border border-gray-200 object-cover">

            </div>
        @endif

    </div>

@endsection
