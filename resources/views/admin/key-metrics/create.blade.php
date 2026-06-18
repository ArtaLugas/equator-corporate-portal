@extends('admin.layouts.app')

@section('title', 'Add Metric')
@section('page-title', 'Add Metric')

@section('content')

    <form action="{{ route('admin.key-metrics.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.key-metrics._form')
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.key-metrics.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Save Metric</button>
        </div>
    </form>

@endsection
