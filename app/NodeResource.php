<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NodeResource
{
    static function get()
    {
        $idsToExclude = self::getIdsToExclude();

        return DB::table('users')
            ->select('id', 'name')
            ->where('is_vehikl_member', 1)
            ->whereNotIn('id', $idsToExclude)
            ->get();
    }

    static function getDuplicatedIdsToReplace()
    {
        $users = User::all()
            ->where('is_vehikl_member', 1);

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

    static function getDict()
    {
        return self::get()
            ->mapWithKeys(fn($e) => [$e->id => $e->name]);
    }

    public static function getIdsToExclude($userBlackList = [18, 30, 42, 55, 60, 83, 106]): array
    {
        $duplicatedIdsToReplace = static::getDuplicatedIdsToReplace();

        return array_merge($userBlackList, array_keys($duplicatedIdsToReplace->toArray()));
    }
}
