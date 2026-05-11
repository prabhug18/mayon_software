<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Eligibility extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'applicable_for',
        'is_active'
    ];
}
