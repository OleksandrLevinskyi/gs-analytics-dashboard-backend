<?php

namespace App\Resources;

use Illuminate\Support\Facades\DB;

class EdgeResource
{
    static function get($userBlackList = [18, 30, 42, 55, 60, 83, 106])
    {
        $nodes = NodeResource::get($userBlackList);
        $weightDictionary = self::getWeightDictionary();

        return $nodes->reverse()
            ->flatMap(function ($row) use ($weightDictionary, $nodes) {
                return $nodes
                    ->filter(fn($col) => $row->id >= $col->id)
                    ->map(function ($col) use ($weightDictionary, $row) {
                        return [
                            'source' => $row->id,
                            'target' => $col->id,
                            'weight' => $row->id === $col->id ?
                                0 :
                                $weightDictionary[$col->id . '_' . $row->id] ?? 0,
                        ];
                    });
            });
    }

    static function getWeightDictionary()
    {
        $idsToReplace = NodeResource::getDuplicatedIdsToReplace();

        $data = self::getData();

        $result = $data->mapWithKeys(fn($e) => [self::getDictionaryKey($e, $idsToReplace) => 0]);

        $data->each(fn($e) => $result[self::getDictionaryKey($e, $idsToReplace)] += $e->weight);

        return $result;
    }

    static function getKey($key, $stack)
    {
        return array_key_exists($key, $stack->toArray()) ? $stack[$key] : $key;
    }

    static function getDictionaryKey($e, $idsToReplace): string
    {
        $firstKeyPart = self::getKey($e->source_id, $idsToReplace);
        $secondKeyPart = self::getKey($e->target_id, $idsToReplace);

        if ($firstKeyPart <= $secondKeyPart) {
            return $firstKeyPart . '_' . $secondKeyPart;
        }

        return $secondKeyPart . '_' . $firstKeyPart;
    }

    static function getConnections($userBlackList = [18, 30, 42, 55, 60, 83, 106])
    {
        $connections = NodeResource::get($userBlackList)->mapWithKeys(fn($e) => [$e->id => []])->toArray();
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
