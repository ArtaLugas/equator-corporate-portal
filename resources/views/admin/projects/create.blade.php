@extends('admin.layouts.app')

@section('title', 'Create Project')

@section('page-title', 'Create Project')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">

            @csrf

            @include('admin.projects._form')

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.projects.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">
                    Cancel
                </a>

                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">
                    Save Project
                </button>

            </div>

        </form>

    </div>

@endsection
