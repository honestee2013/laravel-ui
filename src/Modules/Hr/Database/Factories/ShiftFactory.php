<?php

namespace App\Modules\Hr\Database\Factories;



use App\Modules\Hr\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;


class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $startTime = $this->faker->time('H:i:s');
        $endTime = $this->faker->time('H:i:s');
        $isOvernight = Carbon::parse($startTime)->greaterThan(Carbon::parse($endTime));

        return [
            'name' => $this->faker->unique()->word . ' Shift',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_overnight' => $isOvernight,
            'is_active' => true,
        ];

      
    }

    public function dayShift(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Day Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_overnight' => false,
            ];
        });
    }

    public function nightShift(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'is_overnight' => true,
            ];
        });
    }
}
