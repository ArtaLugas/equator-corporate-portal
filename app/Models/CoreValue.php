<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;

class CoreValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'display_order',
        'status',
    ];

    /**
     * Sanitize the rich-text body on write — it is rendered to the public with
     * {!! !!} on the About page, so purifying at the source closes stored XSS.
     */
    protected function description(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => $value === null ? null : Purifier::clean($value)
        );
    }
}
