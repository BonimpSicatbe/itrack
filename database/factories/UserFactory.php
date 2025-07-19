<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => fake()->firstName(),
            'middlename' => fake()->optional()->firstName(),
            'lastname' => fake()->lastName(),
            'extensionname' => fake()->optional()->suffix(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'college_id' => $collegeId = \App\Models\College::inRandomOrder()->value('id'),
            'department_id' => \App\Models\Department::where('college_id', $collegeId)->inRandomOrder()->value('id'),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
