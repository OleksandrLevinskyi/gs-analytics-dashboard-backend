<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleProviderCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        /** @var User $user */
        $user = User::query()->where('email', $githubUser->email)->where('is_vehikl_member', 1)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid user.'], 422);
        }

        Auth::login($user);

        $hash = md5("github.{$githubUser->email}");
        Cache::put("social_user.{$hash}", $user);
//        $token = $user->createToken('token-name')->plainTextToken;
//
//        return response()->json($user, 200, ['Access-Token' => $token]);
        return redirect(config('app.spa_url'));
    }

    public function getSocialUser($userHash)
    {
        return response()->json(
            Cache::get("social_user.{$userHash}")
        );
    }
}
