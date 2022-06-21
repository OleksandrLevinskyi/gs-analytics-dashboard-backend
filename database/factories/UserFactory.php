<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
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
