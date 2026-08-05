@extends('admin.layouts.app')

@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')

    <div class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Edit Role: {{ $role->name }}</h1>
        <p class="mt-1.5 text-sm font-medium text-gray-500">Adjust the permissions this role grants.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('admin.roles._form', ['submitLabel' => 'Save Changes'])
    </form>

@endsection
