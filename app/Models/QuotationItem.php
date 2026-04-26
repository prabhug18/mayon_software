<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'service_id',
        'service_item_id',
        'description',
        'unit',
        'quantity',
        'base_cost',
        'margin_type',
        'margin_value',
        'selling_rate',
        'gst_percentage',
        'line_total',
        'manual_service_name',
        'manual_item_name'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'margin_value' => 'decimal:2',
        'selling_rate' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'line_total' => 'decimal:2'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function vendorCost()
    {
        return $this->hasOne(QuotationVendorCost::class);
    }
}
