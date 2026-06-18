<div class="space-y-6">

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Social Link</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A social media platform link for the public site.</p>
        </div>

        @php
            // Daftar platform → nama ikon Bootstrap Icons (bi-*).
            $socialIcons = [
                'instagram' => 'Instagram',
                'facebook' => 'Facebook',
                'linkedin' => 'LinkedIn',
                'youtube' => 'YouTube',
                'twitter-x' => 'X (Twitter)',
                'tiktok' => 'TikTok',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'threads' => 'Threads',
                'pinterest' => 'Pinterest',
                'github' => 'GitHub',
                'globe' => 'Website',
            ];
            $currentIcon = old('icon_class', $socialLink->icon_class ?? '');
        @endphp

        <div class="space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="platform" label="Platform"
                    :value="old('platform', $socialLink->platform ?? '')" placeholder="e.g. Instagram" required />

                <x-admin.form.select name="icon_class" label="Icon (Platform)">
                    <option value="">— Select platform —</option>
                    @foreach ($socialIcons as $val => $label)
                        <option value="{{ $val }}" @selected($currentIcon === $val)>{{ $label }}</option>
                    @endforeach
                    {{-- Pertahankan nilai lama yang tidak ada di daftar --}}
                    @if ($currentIcon && !array_key_exists($currentIcon, $socialIcons))
                        <option value="{{ $currentIcon }}" selected>{{ $currentIcon }} (custom)</option>
                    @endif
                </x-admin.form.select>
            </div>

            {{-- Preview ikon (Bootstrap Icons) --}}
            <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <i id="icon-preview" class="bi bi-{{ $currentIcon ?: 'link-45deg' }} text-2xl text-equator-text"></i>
                </div>
                <p class="flex-1 text-xs text-gray-500">
                    Ikon diambil dari <span class="font-semibold text-equator-text">Bootstrap Icons</span> sesuai platform
                    yang dipilih, dan inilah yang tampil di footer situs publik.
                </p>
            </div>

            <x-admin.form.input name="url" label="URL" type="url"
                :value="old('url', $socialLink->url ?? '')" placeholder="https://instagram.com/yourpage" required />

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="display_order" label="Display Order" type="number" min="0"
                    :value="old('display_order', $socialLink->display_order ?? 0)" placeholder="0" />

                <x-admin.form.select name="status" label="Status" required>
                    <option value="active" {{ old('status', $socialLink->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $socialLink->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </x-admin.form.select>
            </div>

        </div>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('icon_class');
            const preview = document.getElementById('icon-preview');

            if (!select || !preview) {
                return;
            }

            const update = () => {
                preview.className = 'bi bi-' + (select.value || 'link-45deg') + ' text-2xl text-equator-text';
            };

            select.addEventListener('change', update);
            update();
        });
    </script>
@endpush
