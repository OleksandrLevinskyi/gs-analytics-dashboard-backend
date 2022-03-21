<?php

namespace Tests\Unit;

use App\Models\NodeGraph;
use Tests\TestCase;

class NodeGraphTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_returns_correct_data()
    {
        $data = NodeGraph::getData();

        $expected = json_decode(file_get_contents(base_path('tests/Unit/nodegraph.json')), true);

        $this->assertArrayHasKey('edges', $data);
        $this->assertCount(2438, $data['edges']);
        collect($data['edges'])->every(function ($item, $idx) use ($expected) {
            $item = (array)$item;
            $this->assertEquals($item,$expected['edges'][$idx]);
        });

        $this->assertArrayHasKey('nodes', $data);
        $this->assertCount(83, $data['nodes']);
        collect($data['nodes'])->every(function ($item, $idx) use ($expected) {
            $item = (array)$item;
            $this->assertEquals($item,$expected['nodes'][$idx]);
        });
    }
}
