<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'default_description',
        'default_gst_percentage',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_gst_percentage' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function activeItems()
    {
        return $this->hasMany(ServiceItem::class)->where('is_active', true);
    }
}
