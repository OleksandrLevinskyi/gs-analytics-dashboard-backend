<?php

namespace App\Models;

use App\EdgeResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NodeGraph extends Model
{
    use HasFactory;

    static function getData()
    {
        $nodes = DB::table('users')
            ->select('id', 'name')
            ->where('is_vehikl_member', 1)
            ->get();

        $edges = EdgeResource::getEdges();

        return ['nodes' => $nodes, 'edges' => $edges];
    }
}
