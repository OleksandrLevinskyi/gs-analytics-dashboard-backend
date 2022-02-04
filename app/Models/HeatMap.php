<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HeatMap extends Model
{
    use HasFactory;

    static function getData()
    {
        return DB::select(
            DB::raw(
                "SELECT user_name1 source, user_name2 target, COUNT(data.user_id1) weight
                        FROM (
                          SELECT gsu1.user_id user_id1,
                                 gsu1.name user_name1,
                                 gsu2.user_id user_id2,
                                 gsu2.name user_name2
                          FROM (
                            SELECT *
                            FROM growth_session_user gsu
                            JOIN users u ON u.id = gsu.user_id
                            WHERE u.is_vehikl_member = 1
                          ) gsu1
                          JOIN (
                            SELECT *
                            FROM growth_session_user gsu
                            JOIN users u ON u.id = gsu.user_id
                            WHERE u.is_vehikl_member = 1
                          ) gsu2
                          ON gsu1.growth_session_id = gsu2.growth_session_id AND gsu1.user_id <> gsu2.user_id
                            JOIN growth_sessions gs ON gs.id = gsu1.growth_session_id
                        ) data
                        JOIN users u1 ON u1.id = data.user_id1
                        JOIN users u2 ON u2.id = data.user_id2
                        WHERE user_id1 > user_id2
                        GROUP BY data.user_id1, data.user_id2;"
            )
        );
    }
}
