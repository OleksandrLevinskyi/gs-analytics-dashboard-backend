<?php

namespace Database\Seeders;

use App\Models\GrowthSession;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class GrowthSessionSeeder extends Seeder
{
    const NUMBER_OF_FAKE_GROWTH_SESSIONS = 100;
    const NUMBER_OF_FAKE_USERS = 30;
    const NUMBER_OF_MAX_ATTENDEES = 5;
    const SUB_DAYS = 14;

    public function run()
    {
        $users = User::factory()->count(self::NUMBER_OF_FAKE_USERS)->isVehiklMember()->create();

        for ($i = 0; $i < self::NUMBER_OF_FAKE_GROWTH_SESSIONS; $i++) {
            $growthSession = GrowthSession::factory()->create([
                'date' => today()->subDays(rand(0, self::SUB_DAYS)),
            ]);

            $owner = $users[rand(0, self::NUMBER_OF_FAKE_USERS - 1)];
            $owner->growthSessions()->attach($growthSession, ['user_type_id' => UserType::OWNER_ID]);

            collect($users)
                ->random(rand(1, self::NUMBER_OF_MAX_ATTENDEES))
                ->each(fn($user) => $user->growthSessions()->attach($growthSession, ['user_type_id' => UserType::ATTENDEE_ID]));
        }
    }
}
