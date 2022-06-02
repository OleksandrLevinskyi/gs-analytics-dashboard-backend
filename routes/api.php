<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('social_user/{user_hash}', [LoginController::class, 'getSocialUser']);

    Route::get('nodes', [DataController::class, 'getNodes']);
    Route::get('edges', [DataController::class, 'getEdges']);

    Route::prefix('dictionary')->group(function () {
        Route::get('nodes', [DataController::class, 'getNodeDictionary']);
        Route::get('edges', [DataController::class, 'getEdgeDictionary']);
        Route::get('connections', [DataController::class, 'getConnectionDictionary']);
    });
});
