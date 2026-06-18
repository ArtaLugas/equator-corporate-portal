@extends('admin.layouts.app')

@section('title', 'Create Hero Banner')

@section('page-title', 'Create Hero Banner')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.hero-banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">

            @csrf

            @include('admin.hero-banners._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.hero-banners.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white hover:bg-equator-bright">

                    Save Banner

                </button>

            </div>

        </form>

    </div>

@endsection
