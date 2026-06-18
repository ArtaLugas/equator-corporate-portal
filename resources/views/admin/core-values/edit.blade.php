@extends('admin.layouts.app')

@section('title', 'Edit Core Value')

@section('page-title', 'Edit Core Value')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">
                Edit Core Value
            </h1>

            <p class="mt-1 text-sm font-medium text-gray-500">
                Update core value information displayed on the website.
            </p>

        </div>

        <a href="{{ route('admin.core-values.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold transition hover:bg-gray-50">

            Back

        </a>

    </div>

    {{-- FORM --}}
    <form action="{{ route('admin.core-values.update', $coreValue) }}" method="POST">

        @csrf
        @method('PUT')

        @include('admin.core-values._form')

        {{-- ACTIONS --}}
        <div class="mt-8 flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">

            <a href="{{ route('admin.core-values.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold transition hover:bg-gray-50">

                Cancel

            </a>

            <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition hover:bg-equator-bright">

                Update Core Value

            </button>

        </div>

    </form>

@endsection
