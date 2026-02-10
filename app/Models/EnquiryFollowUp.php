<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryFollowUp extends Model
{
    use HasFactory;

    protected $table = 'enquiry_follow_ups';

    protected $fillable = ['enquiry_id','scheduled_at','notes','created_by'];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function enquiry(){ return $this->belongsTo(Enquiry::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class,'created_by'); }
}
