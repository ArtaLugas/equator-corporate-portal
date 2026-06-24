@extends('admin.layouts.app')

@section('title', 'Edit Project')

@section('page-title', 'Edit Project')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')
            {{-- Preserve the list page (pagination + filters) the admin came from. --}}
            <input type="hidden" name="return_url"
                value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.projects.index'))) }}">

            @include('admin.projects._form')

            {{-- ACTIONS --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

                <a href="{{ route('admin.projects.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                    Update Project
                </button>

            </div>

        </form>

    </div>

@endsection
