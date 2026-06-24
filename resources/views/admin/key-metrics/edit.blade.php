@extends('admin.layouts.app')

@section('title', 'Edit Metric')
@section('page-title', 'Edit Metric')

@section('content')

    <form action="{{ route('admin.key-metrics.update', $keyMetric) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        {{-- Preserve the list page (pagination + filters) the admin came from. --}}
        <input type="hidden" name="return_url"
            value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.key-metrics.index'))) }}">
        @include('admin.key-metrics._form', ['metric' => $keyMetric])
        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('admin.key-metrics.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Cancel</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Update Metric</button>
        </div>
    </form>

@endsection
