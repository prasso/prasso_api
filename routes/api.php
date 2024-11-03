<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommandController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
Route::post('save_user/{user}', [\App\Http\Controllers\Api\AuthController::class, 'saveUser']);
Route::post('/app/{apptoken}', [\App\Http\Controllers\Api\AuthController::class, 'getAppSettings']);
Route::post('record_login', [\App\Http\Controllers\Api\AuthController::class, 'record_login']);
Route::post('/profile_update_image','\App\Http\Controllers\Api\AuthController@uploadProfileImageApi')->name('api.upload.post.image');
Route::post('/save_enhanced_profile','\App\Http\Controllers\Api\AuthController@saveEnhancedProfile')->name('api.save.enhanced.profile');
    
Route::post('save_app', [\App\Http\Controllers\Api\AppController::class, 'saveApp']);

Route::post('save_subscription', 'StripeController@save_subscription');
Route::post('livestream_activity', 'SitePageController@livestream_activity');
Route::middleware([
    'siteusergroup'
])->group(function () {
    
Route::post('/run-artisan-command', [CommandController::class, 'runCommand']);

});

