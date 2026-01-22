<?php

namespace Modules\CnxEvents\Services;

use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\EventCustomFieldValue;
use Modules\CnxEvents\Entities\DashboardCard;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Calculate KPI for a custom field within date range
     */
    public function calculateCustomFieldKPI($customFieldId, $aggregation = 'sum', Carbon $startDate = null, Carbon $endDate = null)
    {
        $query = EventCustomFieldValue::where('custom_field_id', $customFieldId)
            ->join('cnx_events', 'cnx_event_custom_field_values.event_id', '=', 'cnx_events.id')
            ->where('cnx_events.status', 'confirmed'); // Only confirmed events

        // Apply date filter if provided
        if ($startDate) {
            $query->where('cnx_events.start_datetime', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('cnx_events.start_datetime', '<=', $endDate);
        }

        switch ($aggregation) {
            case 'sum':
                // Convert to numeric and sum
                return $query->selectRaw('SUM(CAST(value AS DECIMAL(10,2))) as total')
                           ->first()->total ?? 0;

            case 'count':
                return $query->count();

            case 'avg':
                return $query->selectRaw('AVG(CAST(value AS DECIMAL(10,2))) as average')
                           ->first()->average ?? 0;

            case 'min':
                return $query->selectRaw('MIN(CAST(value AS DECIMAL(10,2))) as minimum')
                           ->first()->minimum ?? 0;

            case 'max':
                return $query->selectRaw('MAX(CAST(value AS DECIMAL(10,2))) as maximum')
                           ->first()->maximum ?? 0;

            default:
                return 0;
        }
    }

    /**
     * Get available custom fields for analytics (numeric fields only)
     */
    public function getAnalyticFields()
    {
        return CustomField::whereIn('type', CustomField::getNumericTypes())
            ->whereHas('eventValues')
            ->with('departments')
            ->get();
    }

    /**
     * Get dashboard data for all active cards
     */
    public function getDashboardData()
    {
        $cards = DashboardCard::active()->with('user')->get();

        return $cards->map(function ($card) {
            return [
                'id' => $card->id,
                'title' => $card->title,
                'description' => $card->description,
                'value' => $card->getCurrentValue(),
                'period' => $card->period,
                'color' => $card->color,
                'icon' => $card->icon,
                'calculation_type' => $card->calculation_config['type'] ?? 'unknown',
                'created_by' => $card->user->name ?? 'Unknown',
            ];
        });
    }

    /**
     * Create sample dashboard cards with the new system
     */
    public function createSampleCards()
    {
        // Get some numeric custom fields for examples
        $priceField = CustomField::where('name', 'Price')
            ->whereIn('type', CustomField::getNumericTypes())
            ->first();

        $attendeesField = CustomField::where('name', 'Attendees')
            ->whereIn('type', CustomField::getNumericTypes())
            ->first();

        $cards = [];

        // Simple aggregation card
        if ($priceField) {
            $cards[] = DashboardCard::create([
                'title' => 'Monthly Revenue',
                'description' => 'Total revenue for the current month',
                'calculation_config' => [
                    'type' => 'simple',
                    'field_id' => $priceField->id,
                    'aggregation' => 'sum'
                ],
                'period' => 'month',
                'color' => '#28a745',
                'icon' => 'fas fa-dollar-sign',
                'position' => 1,
                'user_id' => 1, // Assuming admin user
            ]);
        }

        // Formula-based card (Price per Person)
        if ($priceField && $attendeesField) {
            $cards[] = DashboardCard::create([
                'title' => 'Average Price per Person',
                'description' => 'Total revenue divided by total attendees',
                'calculation_config' => [
                    'type' => 'formula',
                    'formula' => 'sum(price) / sum(attendees)',
                    'fields' => [
                        'price' => $priceField->id,
                        'attendees' => $attendeesField->id
                    ]
                ],
                'period' => 'month',
                'color' => '#007bff',
                'icon' => 'fas fa-calculator',
                'position' => 2,
                'user_id' => 1,
            ]);
        }

        return $cards;
    }
}