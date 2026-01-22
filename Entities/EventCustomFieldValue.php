<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;

class EventCustomFieldValue extends Model
{
    protected $table = 'cnx_event_custom_field_values';

    protected $fillable = ['event_id', 'custom_field_id', 'value'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    /**
     * Get the display value for this field
     */
    public function getDisplayValueAttribute()
    {
        if (!$this->customField) {
            return $this->value;
        }

        switch ($this->customField->type) {
            case 'multiselect':
                // For multi-select, value is an array, return as comma-separated string
                return is_array($this->value) ? implode(', ', $this->value) : $this->value;
            case 'select':
                // For single select, return the single value
                return $this->value;
            default:
                return $this->value;
        }
    }
}