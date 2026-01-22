<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'cnx_departments';
    protected $fillable = ['name', 'description'];

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, 'cnx_custom_field_department');
    }
}