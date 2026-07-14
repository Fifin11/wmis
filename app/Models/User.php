<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function citizenReports()
    {
        return $this->hasMany(CitizenReport::class, 'citizen_id');
    }

    public function pickupLogs()
    {
        return $this->hasMany(PickupLog::class, 'driver_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'admin_id');
    }

    public function recyclingLeaderboards()
    {
        return $this->hasMany(RecyclingLeaderboard::class, 'citizen_id');
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class, 'user_id');
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'route_assignments', 'driver_id', 'route_id')
                    ->withPivot('assigned_date')
                    ->withTimestamps();
    }
}
