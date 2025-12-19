<?php

namespace App\Modules\Hr\Database\Factories;



use Illuminate\Database\Eloquent\Factories\Factory;

use App\Modules\Hr\Models\BreakRule;
use App\Modules\Hr\Models\ComplianceStandard; // If you have this model


class BreakRuleFactory extends Factory
{
    protected $model = BreakRule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Standard Break', 'Extended Break', 'Short Break']),
            'after_hours' => $this->faker->randomElement([4.00, 5.00, 6.00]),
            'min_shift_minutes' => $this->faker->randomElement([240, 300, 360]), // 4, 5, 6 hours
            'break_duration_minutes' => $this->faker->randomElement([15, 30, 45, 60]),
            'break_type' => $this->faker->randomElement(['paid', 'unpaid']),
            'max_breaks' => $this->faker->randomElement([1, 2]),
            'break_interval_minutes' => $this->faker->randomElement([null, 120, 180, 240]), // E.g., break every 2, 3, 4 hours
            'is_mandatory' => $this->faker->boolean,
            'compliance_standard_id' => null, // Can be set later or use ComplianceStandard::factory()
            'is_active' => true,
        ];
    }

    public function unpaidStandard(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'break_type' => 'unpaid',
                'after_hours' => 4.00,
                'min_shift_minutes' => 240,
                'break_duration_minutes' => 30,
                'max_breaks' => 1,
                'break_interval_minutes' => null,
                'is_mandatory' => true,
            ];
        });
    }
}
