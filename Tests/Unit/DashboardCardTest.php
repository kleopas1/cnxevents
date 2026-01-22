<?php

namespace Modules\CnxEvents\Tests\Unit;

use Tests\TestCase;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\DashboardCard;
use Modules\CnxEvents\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardCardTest extends TestCase
{
    use RefreshDatabase;

    protected $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
    }

    /** @test */
    public function it_can_create_simple_aggregation_card()
    {
        // Create a custom field
        $field = CustomField::create([
            'name' => 'Test Price',
            'type' => 'number',
            'department_id' => 1,
        ]);

        // Create a dashboard card
        $card = DashboardCard::create([
            'title' => 'Test Revenue',
            'description' => 'Test description',
            'calculation_config' => [
                'type' => 'simple',
                'field_id' => $field->id,
                'aggregation' => 'sum'
            ],
            'period' => 'month',
            'color' => '#28a745',
            'icon' => 'fas fa-dollar-sign',
            'position' => 1,
            'user_id' => 1,
        ]);

        $this->assertEquals('Test Revenue', $card->title);
        $this->assertEquals('simple', $card->calculation_config['type']);
        $this->assertEquals($field->id, $card->calculation_config['field_id']);
        $this->assertEquals('sum', $card->calculation_config['aggregation']);
    }

    /** @test */
    public function it_can_create_formula_based_card()
    {
        // Create custom fields
        $priceField = CustomField::create([
            'name' => 'Price',
            'type' => 'number',
            'department_id' => 1,
        ]);

        $attendeesField = CustomField::create([
            'name' => 'Attendees',
            'type' => 'number',
            'department_id' => 1,
        ]);

        // Create a formula-based card
        $card = DashboardCard::create([
            'title' => 'Price per Person',
            'description' => 'Average price per attendee',
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
            'position' => 1,
            'user_id' => 1,
        ]);

        $this->assertEquals('formula', $card->calculation_config['type']);
        $this->assertEquals('sum(price) / sum(attendees)', $card->calculation_config['formula']);
        $this->assertArrayHasKey('price', $card->calculation_config['fields']);
        $this->assertArrayHasKey('attendees', $card->calculation_config['fields']);
    }

    /** @test */
    public function it_can_evaluate_simple_math_expressions()
    {
        $card = new DashboardCard();

        // Test basic arithmetic
        $this->assertEquals(5, $card->evaluateMathExpression('2 + 3'));
        $this->assertEquals(6, $card->evaluateMathExpression('2 * 3'));
        $this->assertEquals(2, $card->evaluateMathExpression('6 / 3'));
        $this->assertEquals(1, $card->evaluateMathExpression('3 - 2'));

        // Test with decimals
        $this->assertEquals(5.5, $card->evaluateMathExpression('2.5 + 3'));
        $this->assertEquals(7.5, $card->evaluateMathExpression('15 / 2'));
    }

    /** @test */
    public function it_handles_division_by_zero_safely()
    {
        $card = new DashboardCard();

        // Should return 0 for division by zero
        $this->assertEquals(0, $card->evaluateMathExpression('10 / 0'));
    }

    /** @test */
    public function it_validates_numeric_fields_only_for_calculations()
    {
        // Create a text field (non-numeric)
        $textField = CustomField::create([
            'name' => 'Text Field',
            'type' => 'text',
            'department_id' => 1,
        ]);

        // Try to create a card with text field - should fail
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must be numeric for calculations');

        DashboardCard::create([
            'title' => 'Invalid Card',
            'description' => 'This should fail',
            'calculation_config' => [
                'type' => 'simple',
                'field_id' => $textField->id,
                'aggregation' => 'sum'
            ],
            'period' => 'month',
            'color' => '#28a745',
            'icon' => 'fas fa-chart-bar',
            'position' => 1,
            'user_id' => 1,
        ]);
    }

    /** @test */
    public function it_accepts_numeric_fields_for_calculations()
    {
        // Create numeric fields
        $priceField = CustomField::create([
            'name' => 'Price',
            'type' => 'decimal',
            'department_id' => 1,
        ]);

        $attendeesField = CustomField::create([
            'name' => 'Attendees',
            'type' => 'integer',
            'department_id' => 1,
        ]);

        // Should work with decimal field
        $card1 = DashboardCard::create([
            'title' => 'Revenue',
            'description' => 'Total revenue',
            'calculation_config' => [
                'type' => 'simple',
                'field_id' => $priceField->id,
                'aggregation' => 'sum'
            ],
            'period' => 'month',
            'color' => '#28a745',
            'icon' => 'fas fa-dollar-sign',
            'position' => 1,
            'user_id' => 1,
        ]);

        $this->assertEquals('Revenue', $card1->title);

        // Should work with formula using both numeric fields
        $card2 = DashboardCard::create([
            'title' => 'Price per Person',
            'description' => 'Average price per attendee',
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

        $this->assertEquals('Price per Person', $card2->title);
    }

    /** @test */
    public function it_provides_available_field_types()
    {
        $types = CustomField::getAvailableTypes();

        $this->assertArrayHasKey('text', $types);
        $this->assertArrayHasKey('select', $types);
        $this->assertArrayHasKey('date', $types);
        $this->assertArrayHasKey('integer', $types);
        $this->assertArrayHasKey('decimal', $types);

        $this->assertEquals('Integer Number', $types['integer']);
        $this->assertEquals('Decimal Number', $types['decimal']);
    }

    /** @test */
    public function it_identifies_numeric_field_types()
    {
        $numericTypes = CustomField::getNumericTypes();

        $this->assertContains('integer', $numericTypes);
        $this->assertContains('decimal', $numericTypes);
        $this->assertNotContains('text', $numericTypes);
        $this->assertNotContains('select', $numericTypes);
        $this->assertNotContains('date', $numericTypes);
    }

    /** @test */
    public function it_filters_analytics_fields_to_numeric_only()
    {
        // Create different field types
        $textField = CustomField::create([
            'name' => 'Text Field',
            'type' => 'text',
            'department_id' => 1,
        ]);

        $decimalField = CustomField::create([
            'name' => 'Decimal Field',
            'type' => 'decimal',
            'department_id' => 1,
        ]);

        $integerField = CustomField::create([
            'name' => 'Integer Field',
            'type' => 'integer',
            'department_id' => 1,
        ]);

        $analyticsFields = $this->analyticsService->getAnalyticFields();

        // Should only include numeric fields
        $fieldIds = $analyticsFields->pluck('id')->toArray();

        $this->assertContains($decimalField->id, $fieldIds);
        $this->assertContains($integerField->id, $fieldIds);
        $this->assertNotContains($textField->id, $fieldIds);
    }
}