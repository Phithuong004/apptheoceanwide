<?php
namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class SprintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name'       => 'Sprint ' . $this->faker->numberBetween(1, 20),
            'goal'       => $this->faker->sentence(),
            'status'     => 'planning',
            'start_date' => now()->startOfWeek(),
            'end_date'   => now()->addWeeks(2)->endOfWeek(),
            'capacity'   => $this->faker->numberBetween(20, 80),
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status'     => 'active',
            'started_at' => now()->subDays(3),
        ]);
    }
}
