<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CnxEvents\Services\ReportService;

class Report extends Model
{
    protected $table = 'cnx_reports';

    protected $fillable = [
        'name', 'description', 'filters', 'columns',
        'group_by', 'chart_type', 'public', 'user_id'
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'group_by' => 'array',
        'public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Generate the report data
     */
    public function generateData()
    {
        $service = new ReportService();
        return $service->generateReport($this);
    }

    /**
     * Get available columns for reports
     */
    public static function getAvailableColumns()
    {
        return [
            // Event fields
            'event_title' => 'Event Title',
            'event_description' => 'Event Description',
            'start_datetime' => 'Start Date',
            'end_datetime' => 'End Date',
            'status' => 'Status',
            'venue_name' => 'Venue',
            'client_name' => 'Client Name',
            'client_email' => 'Client Email',
            // Custom fields will be added dynamically
        ];
    }
}