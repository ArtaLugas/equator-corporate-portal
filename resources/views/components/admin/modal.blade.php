<div x-data="{ open: false }" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.away="open = false" class="w-full max-w-lg rounded-2xl bg-white p-6">
        <h2 class="mb-4 text-xl font-bold">Modal Title</h2>
    </div>

    <p class="text-gray-600">
        Modal content.
    </p>
</div>
