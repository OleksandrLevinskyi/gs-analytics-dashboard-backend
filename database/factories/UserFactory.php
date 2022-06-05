<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'github_nickname' => $this->faker->userName,
            'avatar' => $this->faker->imageUrl,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => $this->faker->password,
            'remember_token' => Str::random(10),
            'is_vehikl_member' => false,
        ];
    }

    public function isVehiklMember(bool $vehiklMember = true)
    {
        return $this->state([
            'is_vehikl_member' => $vehiklMember
        ]);
    }
}
