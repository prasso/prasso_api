<?php

use App\Http\Livewire\Prasso\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User2Controller;

Route::get('logout', function () {
    return redirect('/login');
});
Route::get('/', 'SitePageController@index');

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
Route::get('/page/faqs', 'SiteController@seeFaqs')->name('see-faqs');
Route::post('/question', 'SiteController@processQuestion')->name('send-question');
Route::post('/newsletter', 'EmailController@registerEmailForNewsletter')->name('newsletter-register');
Route::get('/confirm_newsletter_subscription', 'EmailController@confirm_newsletter_subscription')->name('confirm-newsletter-subscription');


Route::get('/page/{section}','SitePageController@viewSitePage');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


Route::group(['middleware'=> 'instructorusergroup'], function() {

    Route::get('/Prasso/dashboard', [Dashboard::class, '__invoke']);
 
    Route::get('/team/{teamid}', 'TeamController@editTeam')->name('team.edit');
    Route::get('/team/{teamid}/messages', 'TeamController@setupForTeamMessages')->name('team.getmessages');
    Route::post('/team/{teamid}/postmessages', 'TeamController@processTeamMessages')->name('team.postmessages');

    Route::get('/team/{teamid}/apps', 'TeamController@index')->name('apps.show');
    Route::get('/team/{teamid}/apps/{appid}', 'TeamController@editApp')->name('apps.edit');
    Route::get('/team/{teamid}/apps/{appid}/activate', 'TeamController@activateApp')->name('apps.activate');
    Route::get('/team/{teamid}/apps/{appid}/tabs/{tabid}', 'TeamController@editTab')->name('apps.edit-tab');
    Route::get('/team/{teamid}/apps/{appid}/tabs/new', 'TeamController@addTab')->name('apps.add-tab');
    Route::get('/team/{teamid}/apps/{appid}/delete', 'TeamController@deleteApp')->name('apps.delete');
    Route::get('/team/{teamid}/apps/{appid}/tabs/{tabid}/delete', 'TeamController@deleteTab')->name('apps.delete-tab');

    Route::post('/account/initial-profile', 'User2Controller@setupProfile')->name('account.initialprofile');
    Route::get('/profile/Prasso_profile','User2Controller@Prasso_profile')->name('Prasso.profile');
    Route::post('/profile/profile_update_image','User2Controller@uploadProfileImage')->name('upload.post.image');
    

});

Route::group(['middleware'=> 'superadmin'], function() {

Route::get('/sitepages/{siteid}', 'SitePageController@editSitePages');
Route::post('/save-site-page', 'SitePageController@saveSitePage');
Route::get('/visual-editor/{pageid}', 'SitePageController@visualEditor');

Route::resource('Sites', SiteController::class);
Route::get('/sites', 'SiteController@index')->name('sites.show');
});


