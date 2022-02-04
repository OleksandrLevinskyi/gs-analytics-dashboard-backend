<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Array_;
use function PHPUnit\Framework\isEmpty;

class HeatMap extends Model
{
    use HasFactory;

    static function getData()
    {
        $users = DB::table('users')
            ->select('id')
            ->where('is_vehikl_member', 1)
            ->get()
            ->pluck('id');

        $connections = DB::select(
            DB::raw(
                "SELECT user_id1 source, user_id2 target, COUNT(data.user_id1) weight
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

        $result = [];
        foreach ($users as $row) {
            foreach ($users as $col) {
                if ($row > $col) {
                    $result[] = ['source' => $row, 'target' => $col, 'weight' => 0];
                }
            }
        }


        foreach ($result as &$elem) {
            foreach ($connections as $connection) {
                if ($connection->source > $connection->target ?
                    $elem['source'] === $connection->source && $elem['target'] === $connection->target :
                    $elem['source'] === $connection->target && $elem['target'] === $connection->source) {
                    $elem['weight'] = $connection->weight;
                }
            }
        }

        return $result;
    }
}
