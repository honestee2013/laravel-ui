<?php

namespace App\Modules\Hr\Database\Factories;

use App\Modules\Hr\Models\EmployeeProfile;
use App\Modules\Hr\Models\Shift;
use App\Modules\Access\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeProfileFactory extends Factory
{
    protected $model = EmployeeProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 'EMP-' . now()->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'shift_id' => Shift::factory(),
            'role_id' => Role::factory(),
            'job_title_id' => null,
            'department' => $this->faker->word,
            'designation' => $this->faker->jobTitle,
            'employment_type' => $this->faker->randomElement(['full-time', 'part-time', 'contract']),
            'hourly_rate' => 100,// $this->faker->randomFloat(2, 100, 500),
            'work_location' => $this->faker->city,
            'joining_date' => $this->faker->date(),
            'termination_date' => null,
            'notes' => $this->faker->optional()->sentence,
        ];
    }
}
