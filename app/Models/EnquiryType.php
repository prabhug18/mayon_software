<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnquiryType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enquiry_types';
    protected $fillable = ['name'];
}
