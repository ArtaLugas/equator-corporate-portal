@extends('admin.layouts.app')

@section('title', 'Edit News')
@section('page-title', 'Edit News')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            @include('admin.news._form')

            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">
                <a href="{{ route('admin.news.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Update News</button>
            </div>

        </form>

    </div>

@endsection
