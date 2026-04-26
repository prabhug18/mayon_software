<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationVendorCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_item_id',
        'vendor_id',
        'vendor_rate',
        'vendor_total',
        'vendor_gst'
    ];

    protected $casts = [
        'vendor_rate' => 'decimal:2',
        'vendor_total' => 'decimal:2',
        'vendor_gst' => 'decimal:2'
    ];

    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
