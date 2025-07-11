<?php

namespace Database\Factories;

use App\Models\Requirement;
use App\Models\User;
use App\Notifications\RequirementNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

class RequirementFactory extends Factory
{
    protected $model = Requirement::class;

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
            'created_by' => User::inRandomOrder()->value('id'),
            'updated_by' => null,
            'archived_by' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Requirement $requirement) {
            $users = User::where('college_id', $requirement->id)
            ->orWhere('department_id', $requirement->id)
            ->get();

            foreach ($users as $index => $user) {
                $user->notify(new RequirementNotification(Auth::user(), $requirement));
            }
        });
    }
}
