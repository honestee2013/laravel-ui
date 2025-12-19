<?php

namespace App\Modules\Hr\Database\Factories;



use App\Modules\Hr\Models\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\Factory;





class DayOfWeekFactory extends Factory
{
    protected $model = DayOfWeek::class;

    public function definition(): array
    {
        $dayOfWeek = $this->faker->unique()->dayOfWeek;
        return [
            'name' => $dayOfWeek,
            'short_name' => substr($dayOfWeek, 0, 3),
        ];
    }

    // Helper states for specific days
    public function monday(): Factory { return $this->state(['id' => 1, 'name' => 'Monday', 'short_name' => 'Mon']); }
    public function tuesday(): Factory { return $this->state(['id' => 2, 'name' => 'Tuesday', 'short_name' => 'Tue']); }
    public function wednesday(): Factory { return $this->state(['id' => 3, 'name' => 'Wednesday', 'short_name' => 'Wed']); }
    public function thursday(): Factory { return $this->state(['id' => 4, 'name' => 'Thursday', 'short_name' => 'Thu']); }
    public function friday(): Factory { return $this->state(['id' => 5, 'name' => 'Friday', 'short_name' => 'Fri']); }
    public function saturday(): Factory { return $this->state(['id' => 6, 'name' => 'Saturday', 'short_name' => 'Sat']); }
    public function sunday(): Factory { return $this->state(['id' => 7, 'name' => 'Sunday', 'short_name' => 'Sun']); }
}