<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared helper for translation seeders: apply a [matchValue => attributes] map,
 * updating each matched record via Eloquent ->update() so the HasTranslations
 * trait sanitizes HTML fields per locale. Missing matches are skipped silently
 * (idempotent + safe across environments).
 */
trait AppliesTranslations
{
    /**
     * @param  class-string<Model>  $model
     */
    protected function apply(string $model, string $matchColumn, array $map): void
    {
        foreach ($map as $match => $attributes) {
            $model::query()->where($matchColumn, (string) $match)->get()->each->update($attributes);
        }
    }
}
