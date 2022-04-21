<?php

namespace App\Http\Controllers;

use App\EdgeResource;
use App\NodeResource;

class DataController extends Controller
{
    public function nodes()
    {
        return NodeResource::get();
    }

    public function edges()
    {
        return EdgeResource::get();
    }

    public function dictNodes()
    {
        return NodeResource::getDict();
    }

    public function dictEdges()
    {
        return EdgeResource::getWeightDictionary();
    }

    public function dictConnections()
    {
        return EdgeResource::getConnections();
    }
}
