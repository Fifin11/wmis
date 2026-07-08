<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['admin_id', 'title_en', 'title_si', 'content_en', 'content_si', 'published_at'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}