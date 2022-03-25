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

        $edges = DB::select(
            DB::raw(
                "SELECT gsu1.user_id source, gsu2.user_id target, COUNT(CONCAT(gsu1.user_id, '_', gsu2.user_id)) weight
                        FROM view_name gsu1
                        JOIN view_name gsu2
                        ON gsu1.growth_session_id = gsu2.growth_session_id AND gsu1.user_id <> gsu2.user_id
                        JOIN growth_sessions gs ON gs.id = gsu1.growth_session_id
                        WHERE gsu1.user_id > gsu2.user_id
                        GROUP BY source, target;"
            )
        );

//        $gsu = DB::table('growth_session_user', 'gsu')
//            ->select('*')
//            ->join('users as u', 'u.id', '=', 'gsu.user_id')
//            ->where('u.is_vehikl_member', '=', 1);
//
//        $edges = $gsu
//            ->select(DB::raw("gsu1.user_id source, gsu2.user_id target, COUNT(CONCAT(gsu1.user_id, '_', gsu2.user_id)) weight"))
//            ->joinSub($gsu, 'gsu2', function ($join) {
//                $join->on('gsu1.growth_session_id', '=', 'gsu2.growth_session_id')->on('gsu1.user_id', '<>', 'gsu2.user_id');
//            })
//            ->join('growth_sessions as gs', 'gs.id', '=', 'gsu1.growth_session_id')
//            ->where('gsu1.user_id', '>', 'gsu2.user_id')
//            ->groupBy('source', 'target')
//            ->get();


        return ['nodes' => $nodes, 'edges' => $edges];
    }
}


//https://laravel.com/docs/9.x/queries#subquery-joins
