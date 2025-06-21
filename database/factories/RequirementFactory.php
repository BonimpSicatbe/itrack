<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requirement>
 */
class RequirementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'due' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'target' => $target = $this->faker->randomElement(['college', 'department']),
            'target_id' => $target === 'college'
                ? \App\Models\College::inRandomOrder()->value('id')
                : \App\Models\Department::inRandomOrder()->value('id'),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'created_by' => \App\Models\User::inRandomOrder()->value('id'),
            'updated_by' => null,
            'archived_by' => null,
        ];
    }
}
