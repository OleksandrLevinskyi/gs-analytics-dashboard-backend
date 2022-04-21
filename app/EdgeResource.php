<?php

namespace App;

use Illuminate\Support\Facades\DB;

class EdgeResource
{
    static function get()
    {
        $nodes = NodeResource::get();
        $weightDictionary = self::getWeightDictionary();

        return $nodes->reverse()
            ->flatMap(function ($row) use ($weightDictionary, $nodes) {
                return $nodes
                    ->filter(fn($col) => $row->id >= $col->id)
                    ->map(function ($col) use ($weightDictionary, $row) {
                        return [
                            'source_id' => $row->id,
                            'target_id' => $col->id,
                            'weight' => $weightDictionary[$row->id . '_' . $col->id] ?? 0,
                        ];
                    });
            });
    }

    static function getWeightDictionary()
    {
        $idsToReplace = NodeResource::getDuplicatedIdsToReplace();

        $data = self::getData();

        $result = $data->mapWithKeys(fn($e) => [self::getKey($e->source_id, $idsToReplace) . '_' . self::getKey($e->target_id, $idsToReplace) => 0]);

        $data->each(fn($e) => $result[self::getKey($e->source_id, $idsToReplace) . '_' . self::getKey($e->target_id, $idsToReplace)] += $e->weight);

        return $result;
    }

    static function getKey($key, $stack)
    {
        return array_key_exists($key, $stack->toArray()) ? $stack[$key] : $key;
    }

    static function getConnections($userBlackList = [18, 30, 42, 55, 60, 83, 106])
    {
        $connections = NodeResource::get()->mapWithKeys(fn($e) => [$e->id => []])->toArray();
        $idsToReplace = NodeResource::getDuplicatedIdsToReplace();

        self::getData()
            ->whereNotIn('source_id', $userBlackList)
            ->whereNotIn('target_id', $userBlackList)
            ->each(function ($e) use ($idsToReplace, &$connections) {
                $connections[self::getKey($e->source_id, $idsToReplace)][] = self::getKey($e->target_id, $idsToReplace);
                $connections[self::getKey($e->target_id, $idsToReplace)][] = self::getKey($e->source_id, $idsToReplace);
            });

        return collect($connections)->map(fn($e) => collect($e)->unique()->flatten());
    }

    static function getData()
    {
        return DB::table('growth_session_user', 'gsu1')
            ->select(
                "gsu1.user_id as source_id",
                "gsu2.user_id as target_id",
                DB::raw("COUNT(CONCAT(gsu1.user_id, '_', gsu2.user_id)) weight")
            )
            ->join(
                'growth_session_user as gsu2',
                fn($join) => $join->on('gsu1.growth_session_id', '=', 'gsu2.growth_session_id')->on('gsu1.user_id', '<>', 'gsu2.user_id')
            )
            ->join('users as u1', 'gsu1.user_id', '=', 'u1.id')
            ->join('users as u2', 'gsu2.user_id', '=', 'u2.id')
            ->where('u1.is_vehikl_member', '=', 1)
            ->where('u2.is_vehikl_member', '=', 1)
            ->whereColumn('gsu1.user_id', '>', 'gsu2.user_id')
            ->groupBy('gsu1.user_id', 'gsu2.user_id')
            ->get();
    }
}
