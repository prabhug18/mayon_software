<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'item_name',
        'description',
        'hsn_sac_code',
        'unit_id',
        'unit',
        'default_rate',
        'is_optional',
        'is_active'
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'is_active' => 'boolean',
        'default_rate' => 'decimal:2'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function unitMaster()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
