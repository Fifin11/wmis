<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitizenReport extends Model
{
    protected $fillable = ['citizen_id', 'issue_type', 'location_lat', 'location_lng', 'image_path', 'description', 'status'];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }
}