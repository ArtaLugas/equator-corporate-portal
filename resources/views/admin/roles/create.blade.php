@extends('admin.layouts.app')

@section('title', 'Create Role')
@section('page-title', 'Create Role')

@section('content')

    <div class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Create Role</h1>
        <p class="mt-1.5 text-sm font-medium text-gray-500">Define a role and the permissions it grants.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        @include('admin.roles._form', ['submitLabel' => 'Create Role'])
    </form>

@endsection
