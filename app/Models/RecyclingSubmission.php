<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecyclingSubmission extends Model
{
    protected $fillable = [
        'citizen_id',
        'description',
        'claimed_points',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }
}
