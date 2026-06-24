@extends('admin.layouts.app')

@section('title', 'Edit Hero Banner')

@section('page-title', 'Edit Hero Banner')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.hero-banners.update', $banner) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            {{-- Preserve the list page (pagination + filters) the admin came from. --}}
            <input type="hidden" name="return_url"
                value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.hero-banners.index'))) }}">

            @include('admin.hero-banners._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                {{-- CANCEL --}}
                <a href="{{ route('admin.hero-banners.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold transition hover:bg-gray-50">

                    Cancel

                </a>

                {{-- UPDATE --}}
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">

                    Update Banner

                </button>

            </div>

        </form>

    </div>

@endsection
