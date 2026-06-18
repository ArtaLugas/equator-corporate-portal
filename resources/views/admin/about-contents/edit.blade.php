@extends('admin.layouts.app')

@section('title', 'Edit About Content')

@section('page-title', 'Edit About Content')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.about-contents.update', $content) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            @include('admin.about-contents._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.about-contents.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white hover:bg-equator-bright">

                    Update Content

                </button>

            </div>

        </form>

    </div>

@endsection
