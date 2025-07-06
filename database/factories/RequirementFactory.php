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
        $target = $this->faker->randomElement(['college', 'department']);

        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'due' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'assigned_to' => $target === 'college'
                ? \App\Models\College::inRandomOrder()->value('name')
                : \App\Models\Department::inRandomOrder()->value('name'),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'created_by' => \App\Models\User::inRandomOrder()->value('id'),
            'updated_by' => null,
            'archived_by' => null,
        ];
    }
}
