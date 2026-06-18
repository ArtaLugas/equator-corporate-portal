<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * Single-row settings table.
     */
    protected $guarded = [];

    protected $casts = [
        // SMTP password is sensitive — encrypt at rest.
        'mail_password' => 'encrypted',
    ];

    public const CACHE_KEY = 'app.settings.singleton';

    /*
    |--------------------------------------------------------------------------
    | Singleton Accessor
    |--------------------------------------------------------------------------
    */

    public static function current(): self
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->firstOrCreate([])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Busting
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
