<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('save_user/{user}', [\App\Http\Controllers\Api\AuthController::class, 'saveUser']);
Route::post('/app/{apptoken}', [\App\Http\Controllers\Api\AuthController::class, 'getAppSettings']);
Route::post('record_login', [\App\Http\Controllers\Api\AuthController::class, 'record_login']);

Route::post('save_app', [\App\Http\Controllers\Api\AppController::class, 'saveApp']);
