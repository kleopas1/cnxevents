<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomField extends Model
{
    protected $table = 'cnx_custom_fields';
    protected $fillable = ['name', 'type', 'options', 'is_required', 'position'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Order by position by default
        static::addGlobalScope('orderByPosition', function (Builder $builder) {
            $builder->orderBy('position')->orderBy('id');
        });
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'cnx_custom_field_department');
    }

    public function eventValues()
    {
        return $this->hasMany(EventCustomFieldValue::class);
    }

    public function eventCustomFieldValues()
    {
        return $this->hasMany(EventCustomFieldValue::class);
    }

    /**
     * Check if this field type supports numeric operations
     */
    public function isNumeric()
    {
        return in_array($this->type, ['integer', 'decimal']);
    }

    /**
     * Get available field types
     */
    public static function getAvailableTypes()
    {
        return [
            'text' => 'Text',
            'select' => 'Single Select',
            'multiselect' => 'Multi Select',
            'date' => 'Date',
            'integer' => 'Integer Number',
            'decimal' => 'Decimal Number',
        ];
    }

    /**
     * Get numeric field types only (for dashboard/analytics)
     */
    public static function getNumericTypes()
    {
        return ['integer', 'decimal'];
    }

    /**
     * Get user-friendly type label
     */
    public function getTypeLabel()
    {
        $types = self::getAvailableTypes();
        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Check if this field type supports multiple selections
     */
    public function allowsMultipleValues()
    {
        return $this->type === 'multiselect';
    }

    /**
     * Check if this field type requires options
     */
    public function requiresOptions()
    {
        return in_array($this->type, ['select', 'multiselect']);
    }
}