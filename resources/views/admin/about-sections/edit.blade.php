@extends('admin.layouts.app')

@section('title', 'Edit About Section')

@section('page-title', 'Edit About Section')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.about-sections.update', $aboutSection) }}" method="POST" class="space-y-6">

            @csrf
            @method('PUT')

            @include('admin.about-sections._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                <a href="{{ route('admin.about-sections.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">

                    Cancel

                </a>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-equator-dark px-6 py-3 text-sm font-bold text-white transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright/50 active:scale-[0.98] sm:w-auto">

                    Update Section

                </button>

            </div>

        </form>

    </div>

@endsection
