<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enquiry_id',
        'company_id',
        'quotation_no',
        'quotation_date',
        'valid_till',
        'quotation_type',
        'terms_condition_id',
        'terms_content',
        'subtotal',
        'gst_total',
        'grand_total',
        'status',
        'parent_quotation_id',
        'revision_no',
        'customer_name',
        'customer_address',
        'kind_att',
        'created_by'
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_till' => 'date',
        'subtotal' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'revision_no' => 'integer'
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function termsCondition()
    {
        return $this->belongsTo(TermsCondition::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function parentQuotation()
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions()
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
