<?php

namespace Tests\Unit;

use App\Models\User;
use App\Resources\NodeResource;
use Tests\TestCase;

class NodeResourceTest extends TestCase
{
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

        $result = NodeResource::getDuplicatedIdsToReplace();


        $this->assertEquals(
            [

                $userAccounts[1]['id'] => $userAccounts[0]['id'],
                $userAccounts[3]['id'] => $userAccounts[2]['id'],
                $userAccounts[4]['id'] => $userAccounts[2]['id'],
            ],
            $result->toArray()
        );
    }

    public function test_it_formats_data_correctly()
    {
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
}
