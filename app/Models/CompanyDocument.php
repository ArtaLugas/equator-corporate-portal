<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDocument extends Model
{
    use SoftDeletes;

    public $fillable = [
        'title',
        'slug',
        'file',
        'thumbnail',
        'document_type',
        'description',
        'file_size',
        'download_count',
        'display_order',
        'status',
    ];
}
