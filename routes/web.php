<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('terms', function () {
    return view('terms');
});
Route::get('privacy', function () {
    return view('privacy');
});
Route::get('contact', function () {
    return view('contact');
});
Route::post('/send-email', 'EmailController@sendEmail')->name('send-email');

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/team/{teamid}/apps', 'TeamController@index')->name('apps.show');


