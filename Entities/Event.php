<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Event extends Model
{
    protected $table = 'cnx_events';
    protected $fillable = [
        'title', 'description', 'start_datetime', 'end_datetime', 'setup_datetime', 'venue_release_datetime', 'all_day', 'venue_id', 'status', 'user_id',
        'client_name', 'client_email', 'client_phone', 'client_company'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'setup_datetime' => 'datetime',
        'venue_release_datetime' => 'datetime',
        'all_day' => 'boolean',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function customFieldValues()
    {
        return $this->hasMany(EventCustomFieldValue::class);
    }

    public function getCustomFieldValue($customFieldId)
    {
        return $this->customFieldValues()->where('custom_field_id', $customFieldId)->first();
    }

    public function scopeRequests(Builder $query)
    {
        return $query->where('status', 'request');
    }

    public function scopeConfirmed(Builder $query)
    {
        return $query->where('status', 'confirmed');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($event) {
            if (!$event->all_day) {
                // Validate datetime sequence
                $setup = $event->setup_datetime ? $event->setup_datetime->timestamp : null;
                $start = $event->start_datetime->timestamp;
                $end = $event->end_datetime->timestamp;
                $release = $event->venue_release_datetime ? $event->venue_release_datetime->timestamp : null;

                $errors = [];

                if ($setup && $setup >= $start) {
                    $errors['setup_datetime'] = 'Setup datetime must be before start datetime.';
                }
                if ($start >= $end) {
                    $errors['end_datetime'] = 'Start datetime must be before end datetime.';
                }
                if ($release && $end >= $release) {
                    $errors['venue_release_datetime'] = 'End datetime must be before venue release datetime.';
                }

                if (!empty($errors)) {
                    throw ValidationException::withMessages($errors);
                }
            }
        });
    }
}