<?php
namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $project = Project::factory()->create();
        return [
            'workspace_id'    => $project->workspace_id,
            'project_id'      => $project->id,
            'reporter_id'     => User::factory(),
            'title'           => $this->faker->sentence(5),
            'description'     => $this->faker->paragraph(),
            'status'          => $this->faker->randomElement(['backlog','todo','in_progress','done']),
            'priority'        => $this->faker->randomElement(['low','medium','high']),
            'type'            => $this->faker->randomElement(['task','bug','story']),
            'story_points'    => $this->faker->randomElement([1,2,3,5,8,13]),
            'estimated_hours' => $this->faker->randomFloat(1, 1, 16),
            'position'        => $this->faker->numberBetween(0, 100),
            'due_date'        => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
        ];
    }
}
