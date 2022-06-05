<?php

namespace Database\Factories;

use App\Models\GrowthSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrowthSessionFactory extends Factory
{
    protected $model = GrowthSession::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(2),
            'topic' => $this->faker->sentence,
            'location' => 'anydesk',
            'date' => today(),
            'start_time' => now()->setTime(15, 30),
            'end_time' => now()->setTime(17, 00),
            'attendee_limit' => GrowthSession::NO_LIMIT,
            'is_public' => true
        ];
    }
}
