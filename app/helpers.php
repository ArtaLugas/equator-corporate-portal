<?php

use App\Models\ActivityLog;
use App\Models\Setting;

if (! function_exists('activity_log')) {

    function activity_log(
        string $module,
        string $description
    ): void {

        try {

            ActivityLog::create([
                'admin_id' => auth('admin')->user()?->id,
                'module' => $module,
                'description' => $description,
                'ip_address' => request()?->ip(),
            ]);

        } catch (\Throwable $e) {

            report($e);

        }
    }
}

if (! function_exists('app_setting')) {

    /**
     * Read a value from the single-row settings table (cached).
     */
    function app_setting(string $key, $default = null)
    {
        $value = Setting::current()->{$key} ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }
}

if (! function_exists('primary_office')) {

    /**
     * The canonical contact location — single source of truth for public contact
     * info (address, phone, email, map). Returns the primary OfficeLocation, or the
     * first active one if none is flagged primary. Resolved once per request.
     */
    function primary_office(): ?\App\Models\OfficeLocation
    {
        static $resolved = false;
        static $office = null;

        if ($resolved) {
            return $office;
        }

        $resolved = true;

        if (! \Illuminate\Support\Facades\Schema::hasTable('office_locations')) {
            return $office;
        }

        // ordered() sorts is_primary DESC first, so first() is the primary office.
        return $office = \App\Models\OfficeLocation::active()->ordered()->first();
    }
}

