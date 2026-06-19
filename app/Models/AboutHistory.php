<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;

class AboutHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'title',
        'description',
        'image',
        'display_order',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Sanitize the rich-text body on write — rendered to the public timeline
     * with {!! !!}, so purifying at the source closes stored XSS.
     */
    protected function description(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => $value === null ? null : Purifier::clean($value)
        );
    }
}
