@extends('admin.layouts.app')

@section('title', 'Edit Partner')

@section('page-title', 'Edit Partner')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        {{-- FORM --}}
        <form action="{{ route('admin.partners.update', $partner) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            @csrf
            @method('PUT')

            {{-- Preserve the list page (pagination + filters) the admin came from. --}}
            <input type="hidden" name="return_url"
                value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.partners.index'))) }}">

            {{-- FORM PARTIAL --}}
            @include('admin.partners._form')

            {{-- ACTIONS --}}
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

                {{-- CANCEL --}}
                <a href="{{ route('admin.partners.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">

                    Cancel

                </a>

                {{-- UPDATE --}}
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">

                    Update Partner

                </button>

            </div>

        </form>

    </div>

@endsection
