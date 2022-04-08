<?php

namespace App;
use Illuminate\Support\Facades\DB;

class EdgeResource
{
    static function getEdges()
    {
        return DB::table('view_name', 'gsu1')
            ->select(
                "gsu1.user_id as source_id",
                "gsu2.user_id as target_id",
                "gsu1.name as source",
                "gsu2.name as target",
                DB::raw("COUNT(CONCAT(gsu1.user_id, '_', gsu2.user_id)) weight")
            )
            ->join(
                'view_name as gsu2',
                fn($join) => $join->on('gsu1.growth_session_id', '=', 'gsu2.growth_session_id')->on('gsu1.user_id', '<>', 'gsu2.user_id')
            )
            ->join('growth_sessions as gs', 'gs.id', '=', 'gsu1.growth_session_id')
            ->whereColumn('gsu1.user_id', '>', 'gsu2.user_id')
            ->groupBy('gsu1.user_id', 'gsu2.user_id')
            ->get();
    }
}
