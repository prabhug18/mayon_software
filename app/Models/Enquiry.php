<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['mobile','name','location','enquiry_type_id','description','status','source_id','next_follow_up_at','reminder_notes'];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
    ];

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
}
