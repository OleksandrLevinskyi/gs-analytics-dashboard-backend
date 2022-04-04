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
        $userBlackList = [18, 30, 42, 55, 60, 83, 106];
        return HeatMap::getData($userBlackList);
    }
}
