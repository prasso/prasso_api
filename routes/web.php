<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use App\Http\Controllers\SitePageController;
use App\Http\Controllers\SitePageDataController;
use App\Http\Controllers\Admin\SitePackageController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\User2Controller;
use App\Http\Controllers\MySiteController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiteMediaController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\SitePageDataTemplateController;
use App\Http\Controllers\SiteMapController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TeamInvitationController;

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
    $site = Controller::getClientFromHost();
    return view('contact', compact('site'));
});
Route::post('/send-email/{site}', [EmailController::class, 'sendEmail'])->name('send-email');
Route::get('/page/faqs', 'SiteController@seeFaqs')->name('see-faqs');
Route::post('/question', 'SiteController@processQuestion')->name('send-question');
Route::get('/confirm_newsletter_subscription', 'EmailController@confirm_newsletter_subscription')->name('confirm-newsletter-subscription');

Route::get('/page/component/{component}/{pageid}','SitePageController@loadLiveWireComponent');
Route::get('/page/{section}','SitePageController@viewSitePage');
Route::get('/page/{section}/{dataid}','SitePageController@editSitePageData');
Route::post('/sitepages/{siteid}/{pageid}/lateTemplateData', 'SitePageController@lateTemplateData')->name('site-page.late-template-data');
Route::post('/v1/sites/{site_name}/page_views', 'SitePageController@pageView')->name('site-page.page-view');

Route::get('/give','SitePageController@giveToDonate');

Route::get('/dashboard', 'SitePageController@index')->name('dashboard');

// middleware('auth') ensures user has logged in
Route::get('checkout', [StripeController::class, 'showCheckoutForm'])->name('checkout.form')->middleware('auth');
Route::post('checkout', [StripeController::class, 'purchaseFromCheckout'])->name('checkout.purchase')->middleware('auth');
Route::post('/create-payment-intent', [StripeController::class, 'createPaymentIntent'])->name('create.paymentIntent');
Route::get('/donate', [StripeController::class, 'showDonationForm'])->name('donation.form');
Route::post('/donate', [StripeController::class, 'submitDonation'])->name('donation.submit');

// Image upload route
Route::post('/upload', 'ImageController@upload')->name('image.upload')->middleware(['auth', 'web']);

Route::get('subscribe', [StripeController::class, 'showSubscriptionForm'])->name('subscription.form')->middleware('auth');
Route::post('subscribe', [StripeController::class, 'createSubscription'])->name('subscription.create')->middleware('auth');
Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook']);
Route::get('/payment/setup-intent', function (Request $request) {
    return $request->user()->createSetupIntent();
});
Route::post('/payment', function (Request $request) {
    $user = $request->user();
    $paymentMethod = $request->input('payment_method');

    $user->newSubscription('default', 'plan_id')
        ->create($paymentMethod);
});

Route::get('/calendar', [CalendarController::class, 'view'])->name('calendar.view');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'instructorusergroup'
])->group(function () {
    
    Route::get('/team/{teamid}', 'TeamController@editTeam')->name('team.edit');
    Route::get('/team/{teamid}/messages', 'TeamController@setupForTeamMessages')->name('team.getmessages');
    Route::post('/team/{teamid}/postmessages', 'TeamController@processTeamMessages')->name('team.postmessages');

    Route::get('/team/{teamid}/apps', 'TeamController@index')->name('apps.show');
    Route::get('/team/{teamid}/apps/newsiteandapp', 'TeamController@newSiteAndApp')->name('apps.newsiteandapp');
    Route::get('/team/{teamid}/apps/{appid}', 'TeamController@editApp')->name('apps.edit');
    Route::get('/team/{teamid}/apps/{appid}/activate', 'TeamController@activateApp')->name('apps.activate');
    Route::get('/team/{teamid}/apps/{appid}/tabs/{tabid}', 'TeamController@editTab')->name('apps.edit-tab');
    Route::get('/team/{teamid}/apps/{appid}/tabs/new', 'TeamController@addTab')->name('apps.add-tab');
    Route::get('/team/{teamid}/apps/{appid}/delete', 'TeamController@deleteApp')->name('apps.delete');
    Route::get('/team/{teamid}/apps/{appid}/tabs/{tabid}/delete', 'TeamController@deleteTab')->name('apps.delete-tab');
    Route::post('/team/{teamid}/apps/{appid}/app_logo_image','TeamController@uploadAppIcon')->name('upload.app.icon');
    
    Route::get('/profile/prasso_profile','User2Controller@prasso_profile')->name('prasso.profile');
    Route::post('/profile/profile_update_image','User2Controller@uploadProfileImage')->name('upload.post.image');
    
    Route::get('/site/edit', 'MySiteController@editMySite')->name('site.edit.mysite');
    Route::get('/site/{siteid}/edit', 'MySiteController@editSite')->name('site.edit');
    Route::get('/site/{siteid}/livestream-mtce', 'AdminController@livestreamMtce')->name('site.mtce.livestream');
    Route::get('/site/{siteid}/livestream-mtce/{sitemediaid}', 'SiteMediaController@siteMediaEdit')->name('site.mtce.media.edit');
    Route::post('/site/{siteid}/livestream-mtce/move-to-permanent-storage', 'SiteMediaController@siteMediaCreate')->name('site.mtce.media.create');

    Route::get('/visual-editor/{pageid}', 'SitePageController@visualEditor');
    Route::get('/visual-editor/getCombinedHtml/{pageid}', 'SitePageController@getCombinedHtml');
    Route::post('/site/{siteid}/{pageid}/sitePageDataPost', 'SitePageController@sitePageDataPost');
    Route::post('/images/upload', 'ImageController@upload')->name('images.upload');
    Route::post('/images/confirm-resize', 'ImageController@confirmResize')->name('images.confirm-resize');
    Route::get('/image-library', 'ImageController@index')->name('image.library');
    Route::delete('/site-page-data/{pageid}/{id}', [SitePageDataController::class, 'destroy']);

    Route::get('/getLatLonFromAddress', 'ProxyController@getLatLonFromAddress');

    Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
        ->middleware(['signed'])
        ->name('team-invitations.accept');
});

Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
        'superadmin'
    ])->group(function () {
      
    Route::get('/profile/{userid}/update-user','User2Controller@update_user')->name('profile.updateuser');
   
    Route::get('/site-page-data-templates', 'SitePageDataTemplateController@index')->name('site-page-data-templates.index');
    Route::get('/site-page-data-templates/create', 'SitePageDataTemplateController@create')->name('site-page-data-templates.create');
    Route::get('/site-page-data-templates/{id}/edit', 'SitePageDataTemplateController@edit');
    Route::delete('/site-page-data-templates/{id}', 'SitePageDataTemplateController@destroy');

    Route::get('/sitepages/{siteid}', 'SitePageController@editSitePages')->name('site-page.list');;
    Route::get('/sitepages/{siteid}/{pageid}/read-tsv-into-site-page-data', 'SitePageController@readTsvIntoSitePageData')->name('site-page.read-tsv-into-site-page-data');

    Route::get('/sitepages/{siteId}/{sitePageId}/edit-site-page-json-data', [SitePageController::class, 'editSitePageJsonData'])->name('sitepages.editSitePageJsonData');
    Route::post('/sitepages/{siteId}/{sitePageId}/update-site-page-json-data', [SitePageController::class, 'updateSitePageJsonData'])->name('sitepages.updateSitePageJsonData');
    Route::delete('/sitepages/{siteId}/{sitePageId}/delete-site-page-json-data/{dataId}', [SitePageController::class, 'deleteSitePageJsonData'])
        ->name('sitepages.delete-site-page-json-data');
    
    Route::post('/save-site-page', 'SitePageController@saveSitePage');

    Route::resource('Sites', \App\Http\Controllers\SiteController::class);
    Route::get('/sites', 'SiteController@index')->name('sites.show');

    Route::get('/site-packages', [SitePackageController::class, 'manage'])
    ->name('admin.site-packages.manage');
    Route::get('/sites/{site}/packages', [SitePackageController::class, 'getSitePackages']);

});

Route::get('/sites/{site}/site-map', [SiteMapController::class, 'edit'])
    ->name('sites.site-map.edit');
Route::put('/sites/{site}/site-map', [SiteMapController::class, 'update'])
    ->name('sites.site-map.update');
