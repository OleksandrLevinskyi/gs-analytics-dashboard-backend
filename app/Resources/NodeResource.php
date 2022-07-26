<?php

namespace App\Resources;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NodeResource
{
    static function get($userBlackList = [18, 30, 42, 55, 60, 83, 106])
    {
        $idsToExclude = self::getIdsToExclude($userBlackList);

        return DB::table('users')
            ->select('id', 'name')
            ->where('is_vehikl_member', 1)
            ->whereNotIn('id', $idsToExclude)
            ->get()
            ->map(function ($user) {
                if (Str::length($user->name) > 20) {
                    $user->name = Str::substr($user->name, 0, 17) . '...';
                }

                return $user;
            });
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

    static function getDictitionary()
    {
        return self::get()
            ->mapWithKeys(fn($e) => [$e->id => $e->name]);
    }

    public static function getIdsToExclude($userBlackList = []): array
    {
        $duplicatedIdsToReplace = static::getDuplicatedIdsToReplace();

        return array_merge($userBlackList, array_keys($duplicatedIdsToReplace->toArray()));
    }
}
