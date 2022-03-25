<?php

namespace Tests\Unit;

use App\Models\HeatMap;
use App\Models\NodeGraph;
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
}
