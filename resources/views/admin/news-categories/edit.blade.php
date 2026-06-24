@extends('admin.layouts.app')

@section('title', 'Edit News Category')
@section('page-title', 'Edit News Category')

@section('content')

    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">

        <form action="{{ route('admin.news-categories.update', $newsCategory) }}" method="POST" class="space-y-6"
            x-data="{
                name: @js(old('name', $newsCategory->name)),
                slug: @js($newsCategory->slug),
                generateSlug() {
                    this.slug = this.name.toString().toLowerCase().trim()
                        .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
                        .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
                }
            }" x-effect="generateSlug()">

            @csrf
            @method('PUT')

            {{-- Preserve the list page (pagination + filters) the admin came from. --}}
            <input type="hidden" name="return_url"
                value="{{ old('return_url', guarded_list_url(url()->previous(), route('admin.news-categories.index'))) }}">

            <x-admin.form.input name="name" label="Category Name" x-model="name"
                placeholder="e.g. Company Updates" required />

            <div class="space-y-1.5">
                <label class="block text-xs font-bold tracking-wide text-gray-700">URL Slug (auto)</label>
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-500">
                    <span class="text-gray-400">/</span><span x-text="slug || '...'"></span>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                <a href="{{ route('admin.news-categories.index') }}"
                    class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
                <button type="submit"
                    class="rounded-xl bg-equator-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-equator-bright">Update Category</button>
            </div>

        </form>

    </div>

@endsection
