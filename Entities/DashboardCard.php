<?php

namespace Modules\CnxEvents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CnxEvents\Services\AnalyticsService;
use Carbon\Carbon;

class DashboardCard extends Model
{
    protected $table = 'cnx_dashboard_cards';

    protected $fillable = [
        'title', 'description', 'calculation_config', 'period',
        'color', 'icon', 'position', 'active', 'user_id'
    ];

    protected $casts = [
        'calculation_config' => 'array',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($card) {
            $card->validateCalculationConfig();
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Calculate the current value for this card
     */
    public function getCurrentValue()
    {
        $config = $this->calculation_config;

        if (!$config || !isset($config['type'])) {
            return 0;
        }

        $dates = $this->getPeriodDates();

        switch ($config['type']) {
            case 'simple':
                return $this->calculateSimpleAggregation($config, $dates['start'], $dates['end']);

            case 'formula':
                return $this->calculateFormula($config, $dates['start'], $dates['end']);

            default:
                return 0;
        }
    }

    /**
     * Calculate simple aggregation (single field)
     */
    private function calculateSimpleAggregation($config, $startDate, $endDate)
    {
        if (!isset($config['field_id']) || !isset($config['aggregation'])) {
            return 0;
        }

        $analytics = new AnalyticsService();
        return $analytics->calculateCustomFieldKPI(
            $config['field_id'],
            $config['aggregation'],
            $startDate,
            $endDate
        );
    }

    /**
     * Calculate formula-based aggregation (multiple fields with math)
     */
    private function calculateFormula($config, $startDate, $endDate)
    {
        if (!isset($config['formula']) || !isset($config['fields'])) {
            return 0;
        }

        // Get values for all fields in the formula
        $fieldValues = [];
        $analytics = new AnalyticsService();

        foreach ($config['fields'] as $fieldName => $fieldId) {
            $fieldValues[$fieldName] = $analytics->calculateCustomFieldKPI(
                $fieldId,
                'sum', // Default to sum for formula calculations
                $startDate,
                $endDate
            );
        }

        // Replace field names in formula with actual values
        $formula = $config['formula'];
        foreach ($fieldValues as $fieldName => $value) {
            $formula = str_replace($fieldName, $value, $formula);
        }

        // Evaluate the mathematical expression
        return $this->evaluateMathExpression($formula);
    }

    /**
     * Safely evaluate a mathematical expression
     */
    private function evaluateMathExpression($expression)
    {
        // Remove any dangerous characters and functions
        $expression = preg_replace('/[^0-9+\-\*\/\(\)\.\s]/', '', $expression);

        try {
            // Use eval with safety checks
            if (preg_match('/^[0-9+\-\*\/\(\)\.\s]+$/', $expression)) {
                $result = eval("return ($expression);");
                return is_numeric($result) ? round($result, 2) : 0;
            }
        } catch (\Exception $e) {
            // Log error and return 0
            \Log::error('Dashboard card formula evaluation failed: ' . $expression . ' - ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Get start and end dates for the period
     */
    private function getPeriodDates()
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'quarter':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter()
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            default: // 'all'
                return ['start' => null, 'end' => null];
        }
    }

    /**
     * Scope for active cards ordered by position
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('position');
    }

    /**
     * Validate calculation configuration
     */
    private function validateCalculationConfig()
    {
        $config = $this->calculation_config;

        if (!$config || !isset($config['type'])) {
            throw new \InvalidArgumentException('Calculation config must have a type');
        }

        switch ($config['type']) {
            case 'simple':
                $this->validateSimpleConfig($config);
                break;
            case 'formula':
                $this->validateFormulaConfig($config);
                break;
            default:
                throw new \InvalidArgumentException('Invalid calculation type: ' . $config['type']);
        }
    }

    /**
     * Validate simple aggregation config
     */
    private function validateSimpleConfig($config)
    {
        if (!isset($config['field_id'])) {
            throw new \InvalidArgumentException('Simple calculation requires field_id');
        }

        if (!isset($config['aggregation'])) {
            throw new \InvalidArgumentException('Simple calculation requires aggregation type');
        }

        $field = CustomField::find($config['field_id']);
        if (!$field) {
            throw new \InvalidArgumentException('Field not found: ' . $config['field_id']);
        }

        if (!$field->isNumeric()) {
            throw new \InvalidArgumentException('Field must be numeric for calculations: ' . $field->name);
        }

        $validAggregations = ['sum', 'count', 'avg', 'min', 'max'];
        if (!in_array($config['aggregation'], $validAggregations)) {
            throw new \InvalidArgumentException('Invalid aggregation type: ' . $config['aggregation']);
        }
    }

    /**
     * Validate formula config
     */
    private function validateFormulaConfig($config)
    {
        if (!isset($config['formula'])) {
            throw new \InvalidArgumentException('Formula calculation requires formula');
        }

        if (!isset($config['fields']) || !is_array($config['fields'])) {
            throw new \InvalidArgumentException('Formula calculation requires fields array');
        }

        // Validate all fields are numeric
        foreach ($config['fields'] as $fieldName => $fieldId) {
            $field = CustomField::find($fieldId);
            if (!$field) {
                throw new \InvalidArgumentException('Field not found: ' . $fieldId);
            }

            if (!$field->isNumeric()) {
                throw new \InvalidArgumentException('Field must be numeric for calculations: ' . $field->name);
            }
        }

        // Basic formula validation (prevent dangerous operations)
        $formula = $config['formula'];
        if (preg_match('/[^a-zA-Z0-9+\-\*\/\(\)\.\s]/', $formula)) {
            throw new \InvalidArgumentException('Formula contains invalid characters');
        }
    }
}