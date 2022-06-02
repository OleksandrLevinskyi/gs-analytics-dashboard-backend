<?php

namespace App\Http\Controllers;

use App\EdgeResource;
use App\NodeResource;
use Illuminate\Support\Facades\Cache;

class DataController extends Controller
{
    const ONE_HOUR = 60 * 60;

    public function getNodes()
    {
        return Cache::remember('nodes', self::ONE_HOUR, fn() => NodeResource::get());
    }

    public function getEdges()
    {
        return Cache::remember('edges', self::ONE_HOUR, fn() => EdgeResource::get());
    }

    public function getNodeDictionary()
    {
        return Cache::remember('node_dictionary', self::ONE_HOUR, fn() => NodeResource::getDictitionary());
    }

    public function getEdgeDictionary()
    {
        return Cache::remember('edge_dictionary', self::ONE_HOUR, fn() => EdgeResource::getWeightDictionary());
    }

    public function getConnectionDictionary()
    {
        return Cache::remember('connection_dictionary', self::ONE_HOUR, fn() => EdgeResource::getConnections());
    }
}
