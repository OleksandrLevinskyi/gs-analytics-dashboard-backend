<?php

namespace App\Models;

use App\EdgeResource;
use App\NodeResource;
use App\User;
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
        // get all of the vehikl users
        $users = User::all()
            ->where('is_vehikl_member', 1);

        $duplicatedIdsToReplace = static::getIdsToReplace($users);

        // get all connections including weights
        $connections = EdgeResource::getEdges();

        // loop over connections to replace ids for duplicated accounts
        $connections = $connections->map(function ($connection) use ($duplicatedIdsToReplace) {
            $connection->source_id = Arr::get($duplicatedIdsToReplace, $connection->source_id, $connection->source_id);
            $connection->target_id = Arr::get($duplicatedIdsToReplace, $connection->target_id, $connection->target_id);
            return $connection;
        });

        // create a grid from displayed users
        $idsToExclude = array_merge($userBlackList, array_keys($duplicatedIdsToReplace->toArray()));

        return NodeResource::getNodeGrid($idsToExclude)
            ->each(fn($elem) => $elem->weight = static::getWeight($connections, $elem));
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
        if ($elem->source_id === $elem->target_id) return 0;

        $filteredConnections = $connections
            ->filter(fn($connection) => self::areSameElems($elem, $connection));

        return array_sum($filteredConnections->pluck('weight')->toArray()) ?? 0;
    }

    static function areSameElems($elem1, $elem2): bool
    {
        return ($elem1->source_id === $elem2->source_id && $elem1->target_id === $elem2->target_id) ||
            ($elem1->source_id === $elem2->target_id && $elem1->target_id === $elem2->source_id);
    }
}
