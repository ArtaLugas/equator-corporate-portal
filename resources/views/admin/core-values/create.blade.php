@extends('admin.layouts.app')

@section('title', 'Create Core Value')

@section('page-title', 'Create Core Value')

@section('content')

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.core-values.store') }}" method="POST">

            @csrf

            @include('admin.core-values._form')

            <div class="mt-8 flex justify-end gap-3">

                <a href="{{ route('admin.core-values.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold">

                    Cancel

                </a>

                <button type="submit" class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white">

                    Save Core Value

                </button>

            </div>

        </form>

    </div>

@endsection
