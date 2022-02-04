<?php

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('social_user/{user_hash}', [LoginController::class, 'getSocialUser']);
Route::get('login/github', [LoginController::class, 'redirectToProvider']);
Route::get('login/github/callback', [LoginController::class, 'handleProviderCallback']);

Route::get('nodegraph', [DataController::class, 'nodeGraph']);
Route::get('heatmap', [DataController::class, 'heatMap']);