@extends('admin.layouts.app')

@section('title', 'Add Social Link')
@section('page-title', 'Add Social Link')

@section('content')

    <form action="{{ route('admin.social-links.store') }}" method="POST" class="space-y-6">
        @csrf

        @include('admin.social-links._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.social-links.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Save Link</button>
        </div>
    </form>

@endsection
