<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'po_prefix',
        'contact_person',
        'mobile',
        'email',
        'address',
        'logo',
        'authorized_image',
        'gst_no'
    ];

    // helper to get public URL for the authorized image (if using public assets path)
    public function getAuthorizedImageUrlAttribute()
    {
        if (! $this->authorized_image) return null;
        // if stored under assets/images/uploads (public), return asset path otherwise support storage path
        if (str_starts_with($this->authorized_image, 'assets/') || str_starts_with($this->authorized_image, 'public/')) {
            return asset($this->authorized_image);
        }
        return asset('storage/' . $this->authorized_image);
    }
}
