<?php
namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'workspace_id' => Workspace::factory(),
            'owner_id'     => User::factory(),
            'name'         => ucwords($name),
            'slug'         => Str::slug($name) . '-' . Str::random(4),
            'description'  => $this->faker->paragraph(),
            'color'        => $this->faker->hexColor(),
            'status'       => $this->faker->randomElement(['planning','active','on_hold']),
            'type'         => $this->faker->randomElement(['scrum','kanban']),
            'visibility'   => 'team',
            'start_date'   => now()->subDays(30),
            'end_date'     => now()->addDays(60),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function scrum(): static
    {
        return $this->state(['type' => 'scrum']);
    }
}
