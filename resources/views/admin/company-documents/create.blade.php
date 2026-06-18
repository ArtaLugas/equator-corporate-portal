@extends('admin.layouts.app')

@section('title', 'Create Company Document')

@section('page-title', 'Create Company Document')

@section('content')
    <div class="mx-auto max-w-6xl">

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>

                <h1 class="text-2xl font-extrabold text-equator-text">
                    Create Company Document
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Upload company profile, brochure, capability statement, or other corporate documents.
                </p>

            </div>

            <a href="{{ route('admin.company-documents.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">

                    <path d="m15 18-6-6 6-6" />

                </svg>

                Back to Documents

            </a>

        </div>

        {{-- FORM --}}
        <form action="{{ route('admin.company-documents.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-8">

            @csrf

            @include('admin.company-documents._form')

            {{-- ACTIONS --}}
            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.company-documents.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition hover:bg-equator-bright">

                    Save Document

                </button>

            </div>

        </form>

    </div>

@endsection
