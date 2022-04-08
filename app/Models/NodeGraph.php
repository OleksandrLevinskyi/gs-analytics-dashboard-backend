<?php

namespace App\Models;

use App\EdgeResource;
use App\NodeResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeGraph extends Model
{
    use HasFactory;

    static function getData()
    {
        return [
            'nodes' => NodeResource::getNodes(),
            'edges' => EdgeResource::getEdges()
        ];
    }
}
