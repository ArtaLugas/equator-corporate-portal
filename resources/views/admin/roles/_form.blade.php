@php
    // Group modules by their display group, preserving the module slug.
    $groups = [];
    foreach ($modules as $slug => $meta) {
        $groups[$meta['group']][$slug] = $meta;
    }

    $checked = old('permissions', $granted);
    $isLocked = $locked ?? false;
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">
        Please fix the highlighted fields below.
    </div>
@endif

<div class="space-y-6">

    {{-- ROLE NAME --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <label for="name" class="block text-xs font-bold uppercase tracking-wide text-gray-700">Role Name</label>
        <input type="text" id="name" name="name"
            value="{{ old('name', $role->name ?? '') }}"
            placeholder="e.g. content_editor"
            @disabled($isLocked)
            class="mt-1.5 block w-full max-w-md rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-equator-text placeholder-gray-400 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-1 focus:ring-equator-dark disabled:opacity-60">
        <p class="mt-1.5 text-xs font-medium text-gray-400">Lowercase letters, numbers and underscores only.</p>
        @error('name')
            <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p>
        @enderror

        @if ($isLocked)
            <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                The super admin role always holds every permission and cannot be edited.
            </p>
        @endif
    </div>

    {{-- PERMISSION MATRIX --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6">
        <h3 class="text-sm font-extrabold text-equator-text">Permissions</h3>
        <p class="mt-1 text-xs font-medium text-gray-500">Choose what this role can do in each module.</p>

        <div class="mt-5 space-y-6">
            @foreach ($groups as $groupName => $mods)
                @php $groupPerms = collect($mods)->flatMap(fn ($m, $s) => collect($m['abilities'])->map(fn ($a) => "$s.$a"))->values()->all(); @endphp
                <div x-data="{
                    all: false,
                    init() { this.sync(); },
                    sync() {
                        const boxes = [...$root.querySelectorAll('[data-perm]')];
                        this.all = boxes.length && boxes.every(b => b.checked);
                    },
                    toggle() {
                        $root.querySelectorAll('[data-perm]').forEach(b => { if (!b.disabled) b.checked = this.all; });
                    }
                }">
                    <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2">
                        <h4 class="text-[11px] font-extrabold uppercase tracking-widest text-gray-400">{{ $groupName }}</h4>
                        <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-gray-500">
                            <input type="checkbox" x-model="all" @change="toggle()" @disabled($isLocked)
                                class="rounded border-gray-300 text-equator-dark focus:ring-equator-dark">
                            Select all
                        </label>
                    </div>

                    <div class="space-y-2.5">
                        @foreach ($mods as $slug => $meta)
                            <div class="flex flex-col gap-2 rounded-xl bg-gray-50/60 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-sm font-bold text-gray-700">{{ $meta['label'] }}</span>
                                <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                                    @foreach ($meta['abilities'] as $ability)
                                        @php $perm = "$slug.$ability"; @endphp
                                        <label class="flex cursor-pointer items-center gap-1.5 text-xs font-medium text-gray-600">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" data-perm
                                                @checked(in_array($perm, $checked)) @disabled($isLocked)
                                                @change="sync()"
                                                class="rounded border-gray-300 text-equator-dark focus:ring-equator-dark disabled:opacity-50">
                                            {{ ucfirst($ability) }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.roles.index') }}"
            class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</a>
        <button type="submit"
            class="rounded-xl bg-equator-dark px-6 py-2.5 text-sm font-bold text-white hover:bg-equator-bright">
            {{ $submitLabel }}
        </button>
    </div>
</div>
