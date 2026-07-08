<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecyclingLeaderboard extends Model
{
    protected $table = 'recycling_leaderboard';

    protected $fillable = ['citizen_id', 'points', 'month', 'year', 'rank'];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }
}
