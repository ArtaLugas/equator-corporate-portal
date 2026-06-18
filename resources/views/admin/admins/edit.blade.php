@extends('admin.layouts.app')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin')

@section('content')

    <form action="{{ route('admin.admins.update', $admin) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.admins._form')

        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('admin.admins.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Update Admin</button>
        </div>
    </form>

@endsection
