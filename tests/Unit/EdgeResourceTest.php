<?php

namespace Tests\Unit;

use App\Models\GrowthSession;
use App\Models\User;
use App\Models\UserType;
use App\Resources\EdgeResource;
use Tests\TestCase;

class EdgeResourceTest extends TestCase
{
    public function test_it_counts_a_number_of_times_users_mobbed_together()
    {
        list($accountsOfSameUser, $otherUsers) = $this->generateRecords();

        $result = EdgeResource::getData()->toArray();

        $this->assertEquals([
            (object)[
                "source_id" => $otherUsers[0]->id,
                "target_id" => $accountsOfSameUser[0]->id,
                "weight" => 1,
            ],
            (object)[
                "source_id" => $otherUsers[1]->id,
                "target_id" => $accountsOfSameUser[0]->id,
                "weight" => 1,
            ],
            (object)[
                "source_id" => $otherUsers[2]->id,
                "target_id" => $accountsOfSameUser[1]->id,
                "weight" => 1,
            ]],
            $result);
    }

    public function test_it_merges_data_of_users_with_same_name()
    {
    }

    public function test_it_filters_out_users_outside_of_vehikl()
    {
    }

    public function test_it_sums_up_weights_for_the_same_key()
    {
    }

    public function generateRecords(): array
    {
        $accountsOfSameUser = User::factory()
            ->count(2)
            ->isVehiklMember()
            ->create([
                'name' => 'Bob'
            ]);

        $otherUsers = User::factory()
            ->count(3)
            ->isVehiklMember()
            ->create();

        $growthSessions = GrowthSession::factory()
            ->count(3)
            ->create();

        $otherUsers[0]->growthSessions()->attach($growthSessions[0], ['user_type_id' => UserType::OWNER_ID]);
        $accountsOfSameUser[0]->growthSessions()->attach($growthSessions[0], ['user_type_id' => UserType::ATTENDEE_ID]);

        $otherUsers[1]->growthSessions()->attach($growthSessions[1], ['user_type_id' => UserType::OWNER_ID]);
        $accountsOfSameUser[0]->growthSessions()->attach($growthSessions[1], ['user_type_id' => UserType::ATTENDEE_ID]);

        $accountsOfSameUser[1]->growthSessions()->attach($growthSessions[2], ['user_type_id' => UserType::OWNER_ID]);
        $otherUsers[2]->growthSessions()->attach($growthSessions[2], ['user_type_id' => UserType::ATTENDEE_ID]);
        return array($accountsOfSameUser, $otherUsers);
    }
}
