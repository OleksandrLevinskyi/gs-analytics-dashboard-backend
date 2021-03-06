<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            return redirect(config('app.spa_url') . '/invalid-user');
        }

        Auth::login($user);

        $hash = md5("github.{$githubUser->email}");
        Cache::put("social_user.{$hash}", $githubUser);

        $hashCookie = cookie('hash', $hash, 0, null, null, null, false, true);

        return redirect(config('app.spa_url'))
            ->withCookie($hashCookie);
    }

    public function getSocialUser($userHash)
    {
        $decryptedHash = Str::after(Crypt::decrypt($userHash, false), '|');

        return response()->json(
            Cache::get("social_user.{$decryptedHash}")
        );
    }

    public function logout()
    {
        try {
            Auth::logout();
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        Cookie::queue('grafana_session', null, -1, config('app.grafana_domain'));

        return redirect(config('app.spa_url'))
            ->withoutCookie('hash')
            ->withoutCookie('grafana_session', '/', '.' . config('app.grafana_domain'));
    }
}
