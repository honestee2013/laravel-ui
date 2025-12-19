<?php

namespace App\Modules\Hr\Database\Factories;



use App\Modules\Hr\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Modules\Hr\Models\RoleSchedule;
use App\Modules\Access\Models\Role;
use App\Modules\Hr\Models\DayOfWeek;
use App\Modules\Hr\Models\BreakRule;

use Carbon\Carbon;
use Illuminate\Support\Testing\Fakes\Fake;

class RoleScheduleFactory extends Factory
{
    protected $model = RoleSchedule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word . ' Schedule',
            'role_id' => Role::factory(),
            'shift_id' => Shift::factory(),
            'day_of_week_id' => DayOfWeek::factory(), // Or specific dayOfWeek()
            'override_time_start' => null,
            'override_time_end' => null,
            'overtime_after_hours' => null,
            'max_paid_overtime_hours' => null,
            'max_daily_hours' => null,
            'break_rule_id' => BreakRule::factory()->unpaidStandard(),
            'late_grace_minutes' => 5,
            'early_leave_grace_minutes' => 5,
            'effective_date' => Carbon::now()->subMonths(1)->toDateString(),
            'end_date' => null,
            'is_active' => true,
        ];
    }

    public function withOvertimeAfterHours(float $hours = 8.0): Factory
    {
        return $this->state(function (array $attributes) use ($hours) {
            return [
                'overtime_after_hours' => $hours,
            ];
        });
    }

    public function withMaxPaidOvertime(float $hours = 3.0): Factory
    {
        return $this->state(function (array $attributes) use ($hours) {
            return [
                'max_paid_overtime_hours' => $hours,
            ];
        });
    }

    public function withMaxDailyHours(float $hours = 12.0): Factory
    {
        return $this->state(function (array $attributes) use ($hours) {
            return [
                'max_daily_hours' => $hours,
            ];
        });
    }

    public function withOverrideTimes(string $start, string $end): Factory
    {
        return $this->state(function (array $attributes) use ($start, $end) {
            return [
                'override_time_start' => $start,
                'override_time_end' => $end,
            ];
        });
    }

    public function withoutBreaks(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['break_rule_id' => null];
        });
    }
}
