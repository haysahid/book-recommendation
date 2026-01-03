<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
        $name = $this->faker->name();
        $baseUsername = Str::slug($name, '_');
        $suffix = $this->faker->unique()->numberBetween(1, 9999);
        $uniqueUsername = $baseUsername . '_' . $suffix;

        $emailDomain = $this->faker->freeEmailDomain();
        $uniqueEmail = $uniqueUsername . '@' . $emailDomain;

        return [
            'name' => $name,
            'username' => $uniqueUsername,
            'email' => $uniqueEmail,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'User')->first();
            $user->roles()->attach($role->id);
        });
    }
}
