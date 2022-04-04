<?php

namespace Tests\Unit;

use App\GrowthSession;
use App\Models\HeatMap;
use App\User;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class HeatMapTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_returns_correct_data()
    {
        $data = HeatMap::getData();

        $expected = json_decode(file_get_contents(base_path('tests/Unit/heatmap.json')), true);

        $this->assertCount(2556, $data);
        collect($data)->each(function ($item, $idx) use ($expected) {
            $item = (array)$item;
            $this->assertEquals($item, $expected[$idx]);
        });
    }

    public function test_it_merges_data_of_users_with_same_name()
    {
        $duplicatedUser1 = User::factory()->vehiklMember(true)->create(['name' => 'Bob Smith']);
        $duplicatedUser2 = User::factory()->vehiklMember(true)->create(['name' => 'Bob Smith']);

        $users = User::factory()->count(3)->vehiklMember(true)->create();

        $growthSessions = $users->map(function ($u) {
            $gs = GrowthSession::factory()->create();
            $u->growthSessions()->attach($gs);
            return $gs;
        });

        $duplicatedUser1->growthSessions()->attach($growthSessions[0]);
        $duplicatedUser2->growthSessions()->attach($growthSessions[1]);

        $result = HeatMap::getData();

        $this->assertEquals((array)[
            [
                'source_id' => $users[2]->id,
                'source' => $users[2]->name,
                'target_id' => $duplicatedUser1->id,
                'target' => $duplicatedUser1->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[2]->id,
                'source' => $users[2]->name,
                'target_id' => $users[0]->id,
                'target' => $users[0]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[2]->id,
                'source' => $users[2]->name,
                'target_id' => $users[1]->id,
                'target' => $users[1]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[2]->id,
                'source' => $users[2]->name,
                'target_id' => $users[2]->id,
                'target' => $users[2]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[1]->id,
                'source' => $users[1]->name,
                'target_id' => $duplicatedUser1->id,
                'target' => $duplicatedUser1->name,
                'weight' => 1,
            ],
            [
                'source_id' => $users[1]->id,
                'source' => $users[1]->name,
                'target_id' => $users[0]->id,
                'target' => $users[0]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[1]->id,
                'source' => $users[1]->name,
                'target_id' => $users[1]->id,
                'target' => $users[1]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $users[0]->id,
                'source' => $users[0]->name,
                'target_id' => $duplicatedUser1->id,
                'target' => $duplicatedUser1->name,
                'weight' => 1,
            ],
            [
                'source_id' => $users[0]->id,
                'source' => $users[0]->name,
                'target_id' => $users[0]->id,
                'target' => $users[0]->name,
                'weight' => 0,
            ],
            [
                'source_id' => $duplicatedUser1->id,
                'source' => $duplicatedUser1->name,
                'target_id' => $duplicatedUser1->id,
                'target' => $duplicatedUser1->name,
                'weight' => 0,
            ],
        ], $result->toArray());
    }
}
