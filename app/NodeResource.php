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

    static function getNodeGrid($idsToExclude = [])
    {
        return DB::table('users', 'u1')
            ->crossJoin('users as u2')
            ->select(DB::raw('MIN(u1.id) source_id, u1.name source, MIN(u2.id) target_id, u2.name target, 0 weight'))
            ->where('u1.is_vehikl_member', 1)
            ->where('u2.is_vehikl_member', 1)
            ->whereNotIn('u1.id', $idsToExclude)
            ->whereNotIn('u2.id', $idsToExclude)
            ->whereColumn('u1.name', '>=', 'u2.name')
            ->groupBy('target', 'source')
            ->get();
    }
}
