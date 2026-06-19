<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * Build a URL slug that is unique for the given model — checking trashed
     * rows too, so a soft-deleted record never blocks the unique column. The
     * base is capped so that even with a numeric suffix the result stays within
     * the 191-char slug column.
     *
     * @param  class-string<Model>  $model  Model using SoftDeletes + a `slug` column.
     * @param  int|null  $ignoreId  Row to exclude (the record being updated).
     */
    protected function generateUniqueSlug(string $model, string $name, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($name) ?: 'item', 180, '');
        $slug = $base;
        $suffix = 1;

        while (
            $model::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
