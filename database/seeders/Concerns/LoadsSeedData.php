<?php

namespace Database\Seeders\Concerns;

trait LoadsSeedData
{
    /**
     * Load a JSON data file exported from the legacy SQL dump.
     */
    protected function loadData(string $file): array
    {
        $path = database_path('seeders/data/'.$file.'.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }

    protected function nullable($value)
    {
        return ($value === '' || $value === null) ? null : $value;
    }

    /**
     * Clip a string to fit a varchar column (legacy used 255, we use 191).
     */
    protected function clip(?string $value, int $length = 191): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr($value, 0, $length);
    }
}
