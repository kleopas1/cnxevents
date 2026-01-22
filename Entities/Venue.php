<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'cnx_venues';
    protected $fillable = ['name', 'description', 'color', 'capacity', 'features', 'custom_fields'];

    protected $casts = [
        'features' => 'array',
        'custom_fields' => 'array',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}