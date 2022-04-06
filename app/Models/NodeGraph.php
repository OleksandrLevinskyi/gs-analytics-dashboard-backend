<?php

namespace App\Models;

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

        $gsu = DB::table('growth_session_user', 'gsu1')
            ->select('*')
            ->join('users as u', 'u.id', '=', 'gsu1.user_id')
            ->where('u.is_vehikl_member', '=', 1);

        $edges = $gsu
            ->joinSub($gsu, 'gsu2', function ($join) {
                $join->on('gsu1.growth_session_id', '=', 'gsu2.growth_session_id')->on('gsu1.user_id', '<>', 'gsu2.user_id');
            })
            ->join('growth_sessions as gs', 'gs.id', '=', 'gsu1.growth_session_id')
            ->whereColumn('gsu1.user_id', '>', 'gsu2.user_id')
            ->select(DB::raw("gsu1.user_id source, gsu2.user_id target, COUNT(CONCAT(gsu1.user_id, '_', gsu2.user_id)) weight"))
            ->groupBy('source', 'target')
            ->get();

        return ['nodes' => $nodes, 'edges' => $edges];
    }
}
