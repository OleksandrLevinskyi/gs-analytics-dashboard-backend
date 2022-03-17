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
        $users = collect($data['edges'])->every(function ($item, $idx) use ($expected) {
            $item = (array)$item;
            return $item === $expected['edges'][$idx];
        });
        $this->assertTrue($users);

        $this->assertArrayHasKey('nodes', $data);
        $this->assertCount(83, $data['nodes']);
        $users = collect($data['nodes'])->every(function ($item, $idx) use ($expected) {
            $item = (array)$item;
            return $item === $expected['nodes'][$idx];
        });
        $this->assertTrue($users);
    }
}
