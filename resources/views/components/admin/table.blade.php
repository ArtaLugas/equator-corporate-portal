<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)]">
    {{-- Static table (no horizontal scroll): `w-full` keeps it within the card and
         long text-columns wrap. Short utility columns keep their own per-cell
         `whitespace-nowrap` (dates, badges, action buttons). --}}
    <table class="w-full border-collapse text-left">
        {{ $slot }}
    </table>
</div>
