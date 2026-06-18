@extends('admin.layouts.app')

@section('title', 'Add Admin')
@section('page-title', 'Add Admin')

@section('content')

    <form action="{{ route('admin.admins.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('admin.admins._form')

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.admins.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Create Admin</button>
        </div>
    </form>

@endsection
