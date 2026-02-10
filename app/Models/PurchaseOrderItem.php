<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'uom_id',
        'description',
        'quantity',
        'unit_price',
        'total'
    ];

    public function uom()
    {
        return $this->belongsTo(\App\Models\Uom::class, 'uom_id');
    }
}
