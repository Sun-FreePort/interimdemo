<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/auth/signup', [\App\Http\Controllers\AuthController::class, 'signup']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/info', [\App\Http\Controllers\AuthController::class, 'userInfo']);
    Route::get('/system/dict', [\App\Http\Controllers\AuthController::class, 'systemDict']);
});
