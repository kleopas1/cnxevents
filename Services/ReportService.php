<?php

namespace Modules\CnxEvents\Services;

use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\Report;
use Modules\CnxEvents\Entities\CustomField;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Generate report data based on report configuration
     */
    public function generateReport(Report $report)
    {
        $query = $this->buildQuery($report);

        if ($report->chart_type === 'table') {
            return $this->generateTableData($query, $report);
        } else {
            return $this->generateChartData($query, $report);
        }
    }

    /**
     * Build the base query with filters
     */
    private function buildQuery(Report $report)
    {
        $query = Event::with(['venue', 'customFieldValues.customField']);

        // Apply filters
        $filters = $report->filters ?? [];

        if (isset($filters['date_from'])) {
            $query->where('start_datetime', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('start_datetime', '<=', $filters['date_to']);
        }
        if (isset($filters['venue_id'])) {
            $query->where('venue_id', $filters['venue_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Generate table data
     */
    private function generateTableData(Builder $query, Report $report)
    {
        $events = $query->get();
        $columns = $report->columns ?? ['event_title', 'start_datetime', 'venue_name', 'status'];

        $data = [];
        foreach ($events as $event) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $this->getColumnValue($event, $column);
            }

            $data[] = $row;
        }

        return [
            'type' => 'table',
            'columns' => $columns,
            'data' => $data,
            'total' => count($data)
        ];
    }

    /**
     * Generate chart data
     */
    private function generateChartData(Builder $query, Report $report)
    {
        $groupBy = $report->group_by ?? ['status'];

        // For simplicity, let's group by status and count
        $data = $query->selectRaw('status, COUNT(*) as count')
                     ->groupBy('status')
                     ->get();

        return [
            'type' => 'chart',
            'chart_type' => $report->chart_type,
            'labels' => $data->pluck('status')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    /**
     * Get value for a specific column
     */
    private function getColumnValue(Event $event, $column)
    {
        switch ($column) {
            case 'event_title':
                return $event->title;
            case 'event_description':
                return $event->description;
            case 'start_datetime':
                return $event->start_datetime->format('Y-m-d H:i');
            case 'end_datetime':
                return $event->end_datetime->format('Y-m-d H:i');
            case 'status':
                return ucfirst($event->status);
            case 'venue_name':
                return $event->venue->name ?? '';
            case 'client_name':
                return $event->client_name;
            case 'client_email':
                return $event->client_email;
            default:
                // Check if it's a custom field
                if (str_starts_with($column, 'custom_field_')) {
                    $fieldId = str_replace('custom_field_', '', $column);
                    $value = $event->customFieldValues()->where('custom_field_id', $fieldId)->first();
                    return $value ? $value->value : '';
                }
                return '';
        }
    }
}