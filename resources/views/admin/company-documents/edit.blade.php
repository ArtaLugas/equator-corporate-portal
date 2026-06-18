@extends('admin.layouts.app')

@section('title', 'Edit Company Document')

@section('page-title', 'Edit Company Document')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.company-documents.update', $document) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            @include('admin.company-documents._form')

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">

                {{-- CANCEL --}}
                <a href="{{ route('admin.company-documents.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold transition hover:bg-gray-50">

                    Cancel

                </a>

                {{-- UPDATE --}}
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">

                    Update Document

                </button>

            </div>

        </form>

    </div>

@endsection
