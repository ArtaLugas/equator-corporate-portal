@props(['name', 'label' => 'Gambar', 'preview' => null, 'helpText' => 'SVG, PNG, JPG atau GIF (Maks. 2MB)'])

@php
    $hasError = $errors->has($name);
    $borderColor = $hasError ? 'border-red-400' : 'border-gray-300';
    $ringColor = $hasError ? 'focus-within:ring-red-500/20' : 'focus-within:ring-equator-bright/20';
@endphp

<div x-data="{
    imageUrl: '{{ $preview }}',
    isDragging: false,
    removeExistingImage: false,

    handleFileDrop(e) {
        this.isDragging = false;
        if (e.dataTransfer.files.length > 0) {
            this.$refs.fileInput.files = e.dataTransfer.files;
            this.updatePreview(e.dataTransfer.files[0]);
        }
    },
    handleFileChange(e) {
        if (e.target.files.length > 0) {
            this.updatePreview(e.target.files[0]);
        }
    },
    updatePreview(file) {
        if (!file || !file.type.match('image.*')) return;
        this.imageUrl = URL.createObjectURL(file);
        // Choosing a new image cancels any pending 'remove' intent so the
        // new upload is not discarded on the server.
        this.removeExistingImage = false;
    },
    removeImage() {
        this.imageUrl = null;
        this.removeExistingImage = true;
        this.$refs.fileInput.value = '';
    }
}" class="space-y-2">
    {{-- Label & Action Header --}}
    <div class="flex items-center justify-between">
        <label for="{{ $name }}" class="block text-xs font-bold tracking-wide text-gray-700">
            {{ $label }}
        </label>

        {{-- Tombol Hapus: Menggunakan z-index agar bisa diklik di atas preview --}}
        <button type="button" x-show="imageUrl" x-cloak @click.stop="removeImage"
            class="relative z-50 text-[10px] font-bold uppercase tracking-wider text-red-500 transition-colors hover:text-red-700 focus:outline-none">
            Delete Image
        </button>
    </div>

    {{-- Native Input (Dikeluarkan dari DOM visual agar bisa di-trigger dari mana saja) --}}
    <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/*" x-ref="fileInput"
        @change="handleFileChange" class="hidden">

    <input type="hidden" name="remove_image" :value="removeExistingImage ? 1 : 0">

    {{-- STATE 1: DROPZONE (Hanya tampil saat TIDAK ADA gambar) --}}
    <div x-show="!imageUrl" @click="$refs.fileInput.click()" @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false" @drop.prevent="handleFileDrop"
        class="{{ $borderColor }} {{ $ringColor }} relative flex h-48 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed bg-gray-50 transition-all duration-200"
        :class="isDragging ? 'border-equator-bright bg-equator-bright/5' : 'hover:bg-gray-100 hover:border-gray-400'">
        <div class="pointer-events-none flex flex-col items-center justify-center pb-6 pt-5">
            <svg class="mb-3 h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
            </svg>
            <p class="mb-1 text-sm font-medium text-gray-500">
                <span class="font-bold text-equator-dark">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-400">{{ $helpText }}</p>
        </div>
    </div>

    {{-- STATE 2: ELEGANT PREVIEW (Hanya tampil saat ADA gambar) --}}
    <div x-show="imageUrl" x-cloak @click="$refs.fileInput.click()" @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false" @drop.prevent="handleFileDrop"
        class="group relative w-full cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-sm transition-all"
        :class="isDragging ? 'ring-2 ring-equator-bright' : ''">
        {{-- Efek Backdrop Blur (Mengisi ruang kosong gambar portrait/landscape ekstrim) --}}
        <div class="absolute inset-0 scale-125 bg-cover bg-center opacity-40 blur-xl transition-transform duration-700 group-hover:scale-150"
            :style="`background-image: url('${imageUrl}')`"></div>

        {{-- Area Gambar Aktual --}}
        <div class="relative flex max-h-[32rem] min-h-[16rem] items-center justify-center p-4">
            <img :src="imageUrl"
                class="h-auto max-h-[28rem] w-auto max-w-full rounded-lg border border-gray-200/50 object-contain shadow-lg"
                alt="Preview">
        </div>

        {{-- Overlay Edit Indicator (Muncul saat Hover) --}}
        <div
            class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 backdrop-blur-[2px] transition-all duration-300 group-hover:opacity-100">
            <span
                class="flex items-center gap-2 rounded-lg bg-black/60 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7" />
                    <line x1="16" x2="22" y1="5" y2="5" />
                    <line x1="19" x2="19" y1="2" y2="8" />
                    <circle cx="9" cy="9" r="2" />
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                </svg>
                Change Image
            </span>
        </div>
    </div>

    {{-- Alert Error --}}
    @error($name)
        <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="mt-0.5 shrink-0">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" x2="12" y1="8" y2="12" />
                <line x1="12" x2="12" y1="16" y2="16" />
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
