<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'mobile', 'name', 'email', 'location', 'gstin', 'address',
        'enquiry_type_id', 'description', 'status', 'priority', 'assigned_to',
        'source_id', 'service_id', 'service_item_id', 'next_follow_up_at', 'reminder_notes', 'project_id',
        'fb_lead_id', 'fb_campaign_name', 'fb_form_name', 'fb_platform'
    ];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }

    public function service(){ return $this->belongsTo(Service::class); }
    public function serviceItem(){ return $this->belongsTo(ServiceItem::class, 'service_item_id'); }

    public function project(){ return $this->belongsTo(Project::class); }
    public function enquiryType(){ return $this->belongsTo(EnquiryType::class,'enquiry_type_id'); }
    public function source(){ return $this->belongsTo(Source::class); }

    public function comments()
    {
        return $this->hasMany(EnquiryComment::class);
    }

    public function followUps()
    {
        return $this->hasMany(\App\Models\EnquiryFollowUp::class);
    }

    public function quotations()
    {
        return $this->hasMany(\App\Models\Quotation::class);
    }
}
