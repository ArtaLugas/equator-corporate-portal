@extends('admin.layouts.app')

@section('title', 'Edit Service Category')

@section('page-title', 'Edit Service Category')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.service-categories.update', $category) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            {{-- Preserve the list page (pagination + filters) the admin came from. --}}
            <input type="hidden" name="return_url"
                value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.service-categories.index'))) }}">

            @include('admin.service-categories._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.service-categories.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">

                    Update Category

                </button>

            </div>

        </form>

    </div>

@endsection
