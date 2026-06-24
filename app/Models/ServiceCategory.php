<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    // Only NON-translatable columns are listed; the HasTranslations trait
    // appends the <field>_<locale> columns (name, description, meta_*) to
    // $fillable automatically.
    protected $fillable = [
        'slug',
        'image',
        'display_order',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
