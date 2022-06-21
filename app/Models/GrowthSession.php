<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrowthSession extends Model
{
    use HasFactory;

    const NO_LIMIT = PHP_INT_MAX;
    protected $with = ['attendees', 'comments'];

    protected $appends = ['owner'];

    protected $casts = [
        'start_time' => 'datetime:h:i a',
        'end_time' => 'datetime:h:i a',
        'date' => 'datetime:Y-m-d',
        'attendee_limit' => 'int',
        'is_public' => 'bool'
    ];

    protected $fillable = [
        'title',
        'topic',
        'location',
        'start_time',
        'end_time',
        'date',
        'owner_id',
        'attendee_limit',
        'discord_channel_id',
        'is_public',
    ];

    protected $attributes = [
        'end_time' => '17:00',
        'attendee_limit' => self::NO_LIMIT,
    ];

    public function owners()
    {
        return $this->belongsToMany(User::class)->wherePivot('user_type_id', UserType::OWNER_ID);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class)->wherePivot('user_type_id', UserType::ATTENDEE_ID);
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class)->wherePivot('user_type_id', UserType::WATCHER_ID);
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = Carbon::parse($value)->format('H:i');
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = Carbon::parse($value)->format('H:i');
    }
}
