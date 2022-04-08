<?php

namespace App;
use Illuminate\Support\Facades\DB;

class NodeResource
{
    static function getNodes()
    {
        return DB::table('users')
            ->select('id', 'name')
            ->where('is_vehikl_member', 1)
            ->get();
    }
}
