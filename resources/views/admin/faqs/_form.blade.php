<div class="space-y-6">

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">FAQ Item</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A question and its answer for the public FAQ section.</p>
        </div>

        <div class="space-y-6">

            <x-admin.form.textarea name="question" label="Question" rows="2"
                :value="old('question', $faq->question ?? '')" placeholder="e.g. What services do you provide?" required />

            <x-admin.form.textarea name="answer" label="Answer" rows="6"
                :value="old('answer', $faq->answer ?? '')" placeholder="Write a clear, helpful answer..." required />

            <div class="max-w-xs">
                <x-admin.form.input name="display_order" label="Display Order" type="number" min="0"
                    :value="old('display_order', $faq->display_order ?? 0)" placeholder="0" />
            </div>

        </div>
    </div>

</div>
