<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HeatMap extends Model
{
    use HasFactory;

    static function getData($userBlackList = [18, 30, 42, 55, 60, 83, 106])
    {
        $users = DB::table('users')
            ->selectRaw('id, name')
            ->where('is_vehikl_member', 1)
            ->whereNotIn('id', $userBlackList)
            ->orderBy('id')
            ->get();

        $displayedUsers = DB::table('users')
            ->selectRaw('MIN(id) id, name')
            ->where('is_vehikl_member', 1)
            ->whereNotIn('id', $userBlackList)
            ->orderBy('id')
            ->groupBy('name')
            ->get();

        $connections = DB::table('view_name', 'gsu1')
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

        $idsToReplace = static::getIdsToReplace($users);

        $connections = collect($connections)->map(function ($connection) use ($idsToReplace) {
            $connection->source_id = Arr::get($idsToReplace, $connection->source_id, $connection->source_id);
            $connection->target_id = Arr::get($idsToReplace, $connection->target_id, $connection->target_id);
            return $connection;
        });

        return $displayedUsers
            ->reverse()
            ->flatMap(function ($row) use ($connections, $displayedUsers) {
                return $displayedUsers
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

    static function getIdsToReplace($users)
    {
        return $users->groupBy('name')
            ->filter(fn(Collection $userRecords) => $userRecords->count() > 1)
            ->mapWithKeys(function (Collection $userRecords) use (&$usersWithMultipleAccounts) {
                $minUserId = $userRecords->pluck('id')->min();

                return $userRecords
                    ->where('id', '!==', $minUserId)
                    ->pluck('id')
                    ->mapWithKeys(fn(int $duplicateId) => [$duplicateId => $minUserId]);
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
