<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
    ];

    protected $hidden = [
        'email',
    ];

    protected $casts = [
        'is_vehikl_member' => 'boolean',
    ];

    public function growthSessions()
    {
        return $this->belongsToMany(GrowthSession::class)->wherePivot('user_type_id', UserType::OWNER_ID);
    }
}
