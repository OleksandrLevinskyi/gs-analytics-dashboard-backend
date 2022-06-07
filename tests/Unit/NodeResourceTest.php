<?php

namespace Tests\Unit;

use App\Models\User;
use App\Resources\NodeResource;
use Tests\TestCase;

class NodeResourceTest extends TestCase
{
    public function test_it_merges_users_with_the_same_name()
    {
        $usersWithSameName = User::factory()
            ->count(2)
            ->isVehiklMember()
            ->create([
                'name' => 'Bob'
            ]);

        $result = NodeResource::get([])->toArray();

        $this->assertEquals(
            [
                (object)[
                    'id' => $usersWithSameName[0]->id,
                    'name' => $usersWithSameName[0]->name,
                ]
            ],
            $result
        );
    }

    public function test_it_filters_out_users_outside_of_the_organization()
    {
        User::factory()->create();

        $users = User::factory()
            ->count(2)
            ->isVehiklMember()
            ->create();

        $result = NodeResource::get([])->toArray();

        $this->assertEquals(
            [
                (object)[
                    'id' => $users[0]->id,
                    'name' => $users[0]->name,
                ],
                (object)[
                    'id' => $users[1]->id,
                    'name' => $users[1]->name,
                ]
            ],
            $result
        );
    }

    public function test_it_identifies_users_with_multiple_accounts()
    {
        $userAccounts = [
            ...User::factory()
                ->count(2)
                ->isVehiklMember()
                ->create([
                    'name' => 'Bob'
                ])->toArray(),
            ...User::factory()
                ->count(3)
                ->isVehiklMember()
                ->create([
                    'name' => 'Mike'
                ])->toArray()
        ];

        $result = NodeResource::getDuplicatedIdsToReplace()->toArray();


        $this->assertEquals(
            [

                $userAccounts[1]['id'] => $userAccounts[0]['id'],
                $userAccounts[3]['id'] => $userAccounts[2]['id'],
                $userAccounts[4]['id'] => $userAccounts[2]['id'],
            ],
            $result
        );
    }

    public function test_it_generates_a_node_dictionary()
    {
        $users = User::factory()
            ->count(2)
            ->isVehiklMember()
            ->create();

        $result = NodeResource::getDictitionary()->toArray();

        $this->assertEquals(
            [
                $users[0]->id => $users[0]->name,
                $users[1]->id => $users[1]->name,
            ],
            $result
        );
    }

    public function test_it_gets_ids_to_exlcude()
    {
        $users = [
            ...User::factory()
                ->count(2)
                ->isVehiklMember()
                ->create(),
            ...User::factory()
                ->count(2)
                ->isVehiklMember()
                ->create([
                    'name' => 'Bob'
                ])
        ];

        $result = NodeResource::getIdsToExclude([$users[0]->id]);

        $this->assertEquals(
            [$users[0]->id, $users[3]->id],
            $result
        );
    }
}
