<?php

namespace App\Modules\Hr\Database\Factories;


use App\Modules\Hr\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Modules\Hr\Models\DailyAttendance;
use App\Modules\Hr\Models\Employee;

use Carbon\Carbon;



class DailyAttendanceFactory extends Factory
{
    protected $model = DailyAttendance::class;

    public function definition(): array
    {
        $attendanceTime = $this->faker->dateTimeBetween('-1 week', 'now');
        return [
            'employee_id' => 'EMP-' . now()->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'attendance_time' => $attendanceTime,
            'attendance_type' => $this->faker->randomElement(['check-in', 'check-out']),
            'device_id' => $this->faker->uuid,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'sync_status' => 'synced',
            'sync_attempts' => 1,
            'attendance_date' => Carbon::parse($attendanceTime)->toDateString(),
            // 'checkin_id' => null,
            'scheduled_start' => null,
            'scheduled_end' => null,
            'check_status' => null,
            'minutes_difference' => null,
        ];
    }
}
