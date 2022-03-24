<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HeatMap extends Model
{
    use HasFactory;

    static function getData(): Collection
    {
        $userBlackList = [18, 30, 42, 55, 60, 83, 106];

        $users = DB::table('users')
            ->selectRaw('MIN(id) id, name')
            ->where('is_vehikl_member', 1)
            ->whereNotIn('id', $userBlackList)
            ->orderBy('id')
            ->groupBy('name')
            ->get();

        $connections = DB::select(
            DB::raw(
                "SELECT user_id1 source_id, user_id2 target_id, user_name1 source, user_name2 target, COUNT(data.user_id1) weight
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
                        WHERE user_id1 > user_id2
                        GROUP BY data.user_id1, data.user_id2;"
            )
        );

        return $users
            ->reverse()
            ->flatMap(function ($row) use ($connections, $users) {
                return $users
                    ->filter(fn($col) => $row->id >= $col->id)
                    ->map(function ($col) use ($connections, $row) {
                        $elem = [
                            'source_id' => $row->id,
                            'source' => $row->name,
                            'target_id' => $col->id,
                            'target' => $col->name,
                        ];

                        $elem['weight'] = static::getWeight($connections, $elem);

                        return $elem;
                    });
            });
    }


    static function getWeight($connections, $elem): int
    {
        $connection = collect($connections)
            ->first(function ($connection) use ($elem) {
                return $connection->source_id > $connection->target_id ?
                    $elem['source_id'] === $connection->source_id && $elem['target_id'] === $connection->target_id :
                    $elem['source_id'] === $connection->target_id && $elem['target_id'] === $connection->source_id;
            });

        return $connection->weight ?? 0;
    }
}
