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

        $this->assertEquals(
            [
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
            $result
        );
    }

    public function test_it_filters_out_users_outside_of_the_organization()
    {
        list($accountsOfSameUser, $otherUsers, $growthSessions) = $this->generateRecords();
        $outsider = User::factory()->create();
        $outsider->growthSessions()->attach($growthSessions[0], ['user_type_id' => UserType::ATTENDEE_ID]);

        $result = EdgeResource::getData()->toArray();

        $this->assertEquals(
            [
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
            $result
        );
    }

    public function test_it_generates_a_weight_dictionary_accounting_for_duplicate_accounts()
    {
        list($accountsOfSameUser, $otherUsers) = $this->generateRecords();

        $result = EdgeResource::getWeightDictionary()->toArray();

        $this->assertEquals(
            [
                $otherUsers[0]->id . "_" . $accountsOfSameUser[0]->id => 1,
                $otherUsers[1]->id . "_" . $accountsOfSameUser[0]->id => 1,
                $otherUsers[2]->id . "_" . $accountsOfSameUser[0]->id => 1,
            ],
            $result
        );
    }

    public function test_it_generates_a_weight_dictionary_summing_up_weights_for_the_same_key()
    {
        list($accountsOfSameUser, $otherUsers, $growthSessions) = $this->generateRecords();
        $accountsOfSameUser[1]->growthSessions()->attach($growthSessions[0], ['user_type_id' => UserType::ATTENDEE_ID]);

        $result = EdgeResource::getWeightDictionary()->toArray();

        $this->assertEquals(
            [
                $accountsOfSameUser[0]->id . "_" . $accountsOfSameUser[0]->id => 1,
                $otherUsers[0]->id . "_" . $accountsOfSameUser[0]->id => 2,
                $otherUsers[1]->id . "_" . $accountsOfSameUser[0]->id => 1,
                $otherUsers[2]->id . "_" . $accountsOfSameUser[0]->id => 1,
            ],
            $result
        );
    }

    public function test_it_generates_a_connections_dictionary()
    {
        list($accountsOfSameUser, $otherUsers) = $this->generateRecords();

        $result = EdgeResource::getConnections()->toArray();

        $this->assertEquals(
            [
                $accountsOfSameUser[0]->id => [$otherUsers[0]->id, $otherUsers[1]->id, $otherUsers[2]->id],
                $otherUsers[0]->id => [$accountsOfSameUser[0]->id],
                $otherUsers[1]->id => [$accountsOfSameUser[0]->id],
                $otherUsers[2]->id => [$accountsOfSameUser[0]->id]
            ],
            $result
        );
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
        return array($accountsOfSameUser, $otherUsers, $growthSessions);
    }
}
