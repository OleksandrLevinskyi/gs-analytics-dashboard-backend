<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use http\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirectToProvider()
    {
        try {
            return Socialite::driver('github')->redirect();
        } catch (Exception $e) {
            return response()->json('login_redirect_error', 500);
        }
    }

    public function handleProviderCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            /** @var User $user */
            $user = User::query()->where('email', $githubUser->email)->where('is_vehikl_member', 1)->first();

            if (!$user) {
                return response()->json(['error' => 'Invalid user.'], 422);
            }

            Auth::login($user);

            $hash = md5("github.{$githubUser->email}");
            Cache::put("social_user.{$hash}", $user);

            return redirect(config('app.spa_url'))->withCookie(cookie('hash', $hash, 0, null, null, null, false, true));
        } catch (Exception $e) {
            return response()->json('login_error', 500);
        }
    }

    public function getSocialUser($userHash)
    {
        try {
            $decryptedHash = Str::after(Crypt::decrypt($userHash, false), '|');

            return response()->json(
                Cache::get("social_user.{$decryptedHash}")
            );
        } catch (Exception $e) {
            return response()->json('user_retrieval_error', 500);
        }
    }

    public function logout()
    {
        try {
            Auth::logout();

            return redirect(config('app.spa_url'));
        } catch (Exception $e) {
            return response()->json('logout_error', 500);
        }
    }
}
