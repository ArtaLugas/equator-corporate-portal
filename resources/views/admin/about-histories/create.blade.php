@extends('admin.layouts.app')

@section('title', 'Create History')

@section('page-title', 'Create History')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.about-histories.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf

            @include('admin.about-histories._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.about-histories.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white hover:bg-equator-bright">

                    Save History

                </button>

            </div>

        </form>

    </div>

@endsection
