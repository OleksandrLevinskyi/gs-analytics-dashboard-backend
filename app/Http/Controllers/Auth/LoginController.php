<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
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
            return "error";
        }

        Auth::login($user);

        $hash = md5("github.{$githubUser->email}");
        Cache::put("social_user.{$hash}", $user, now()->addHour());

        return redirect(config('app.spa_url') . '/login/callback' . "#{$hash}");

//        return [
//            'username'=> $githubUser->name
//        ];

        // take $githubUser, pass its credentials to Grafana
    }

    public function getSocialUser($userHash)
    {
        return response()->json(
            Cache::get("social_user.{$userHash}")
        );
    }
}
