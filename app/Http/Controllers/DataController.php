<?php

namespace App\Http\Controllers;

use App\Models\HeatMap;
use App\Models\NodeGraph;

class DataController extends Controller
{
    public function nodeGraph()
    {
        return NodeGraph::getData();
    }

    public function heatMap()
    {
        return HeatMap::getData();
    }
}
