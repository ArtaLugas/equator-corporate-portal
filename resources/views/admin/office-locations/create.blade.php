@extends('admin.layouts.app')

@section('title', 'Add Office Location')
@section('page-title', 'Add Office Location')

@section('content')

    <form action="{{ route('admin.office-locations.store') }}" method="POST" class="space-y-6">
        @csrf

        @include('admin.office-locations._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.office-locations.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Save
                Location</button>
        </div>
    </form>

@endsection
