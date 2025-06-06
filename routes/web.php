<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

    // In routes/web.php (add this temporarily)
    Route::get('/test-s3-ssl', function () {
        $ch = curl_init('https://prassouploads.s3.amazonaws.com/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        // This will capture the verbose output
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Disable SSL verification for testing
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        return response()->json([
            'success' => (bool)$result,
            'http_code' => $httpCode,
            'error' => $error,
            'verbose_log' => $verboseLog,
            'response' => $result ? substr($result, 0, 1000) : null
        ]);
    });
    
    // Test route with CA certificate bundle
    Route::get('/test-s3-ssl-with-cert', function () {
        $ch = curl_init('https://prassouploads.s3.amazonaws.com/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        // This will capture the verbose output
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Use the downloaded CA certificate bundle
        $certPath = base_path('cacert.pem');
        curl_setopt($ch, CURLOPT_CAINFO, $certPath);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        return response()->json([
            'success' => (bool)$result,
            'http_code' => $httpCode,
            'error' => $error,
            'verbose_log' => $verboseLog,
            'response' => $result ? substr($result, 0, 1000) : null,
            'cert_path' => $certPath,
            'cert_exists' => file_exists($certPath)
        ]);
    });
    
    // Test route for S3 file upload with detailed debugging - BASIC VERSION
    Route::get('/test-s3-upload', function () {
        try {
            // Create a simple test file
            $content = 'This is a test file created at ' . date('Y-m-d H:i:s');
            $filePath = 'prasso/test-file-' . time() . '.txt';
            
            // Get S3 client configuration for debugging
            $s3Config = [
                'key' => substr(config('filesystems.disks.s3.key'), 0, 5) . '...',
                'region' => config('filesystems.disks.s3.region'),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'verify' => config('filesystems.disks.s3.options.http.verify') ?? 'Not set'
            ];
            
            // Create S3 client directly for more control
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => base_path('cacert.pem')
                ],
                // Debug mode disabled to prevent headers already sent error
                'debug' => false
            ]);
            
            // Try to upload directly using the S3 client
            $directResult = $s3Client->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $filePath,
                'Body' => $content,
                'ACL' => 'public-read'
            ]);
            
            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'message' => 'File uploaded successfully using direct S3 client',
                's3_config' => $s3Config,
                's3_response' => [
                    'ObjectURL' => $directResult['ObjectURL'] ?? null,
                    'RequestId' => $directResult['RequestId'] ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });
    
    // Comprehensive S3 testing route with multiple methods
    Route::get('/test-s3-comprehensive', function () {
        // Import Log facade if needed
        // Note: 'use' statements must be at the top level, not inside functions
        try {
            
            $results = [];
            $timestamp = time();
            $testContent = 'Test file content created at ' . date('Y-m-d H:i:s');
            
            // Test 1: Using Laravel Storage facade
            try {
                $storagePath = 'prasso/test-laravel-' . $timestamp . '.txt';
                $storageResult = Storage::disk('s3')->put($storagePath, $testContent);
                $results['laravel_storage'] = [
                    'success' => $storageResult,
                    'path' => $storagePath,
                    'error' => null
                ];
            } catch (\Exception $e) {
                $results['laravel_storage'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e)
                ];
            }
            
            // Test 2: Using AWS SDK directly
            try {
                $sdkPath = 'prasso/test-sdk-' . $timestamp . '.txt';
                $s3Client = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region' => config('filesystems.disks.s3.region'),
                    'credentials' => [
                        'key' => config('filesystems.disks.s3.key'),
                        'secret' => config('filesystems.disks.s3.secret'),
                    ],
                    'http' => [
                        'verify' => base_path('cacert.pem')
                    ],
                    'debug' => false
                ]);
                
                $sdkResult = $s3Client->putObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $sdkPath,
                    'Body' => $testContent
                ]);
                
                $results['aws_sdk'] = [
                    'success' => true,
                    'path' => $sdkPath,
                    'response' => [
                        'ObjectURL' => $sdkResult['ObjectURL'] ?? null,
                        'RequestId' => $sdkResult['RequestId'] ?? null,
                    ],
                    'error' => null
                ];
            } catch (\Exception $e) {
                $results['aws_sdk'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e)
                ];
            }
            
            // Test 3: Using cURL directly
            try {
                $curlPath = 'prasso/test-curl-' . $timestamp . '.txt';
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $accessKey = config('filesystems.disks.s3.key');
                $secretKey = config('filesystems.disks.s3.secret');
                
                // Create a pre-signed URL for PUT
                $cmd = $s3Client->getCommand('PutObject', [
                    'Bucket' => $bucket,
                    'Key' => $curlPath,
                ]);
                
                $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
                $presignedUrl = (string)$request->getUri();
                
                // Use cURL to upload with the pre-signed URL
                $ch = curl_init($presignedUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $testContent);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_CAINFO, base_path('cacert.pem'));
                
                $curlResponse = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $results['curl'] = [
                    'success' => ($httpCode >= 200 && $httpCode < 300),
                    'path' => $curlPath,
                    'http_code' => $httpCode,
                    'error' => $curlError ?: null,
                    'response' => $curlResponse
                ];
            } catch (\Exception $e) {
                $results['curl'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e)
                ];
            }
            
            // Configuration information
            $results['config'] = [
                'aws_key' => substr(config('filesystems.disks.s3.key'), 0, 5) . '...',
                'aws_region' => config('filesystems.disks.s3.region'),
                'aws_bucket' => config('filesystems.disks.s3.bucket'),
                'cert_path' => base_path('cacert.pem'),
                'cert_exists' => file_exists(base_path('cacert.pem')),
                'php_version' => phpversion(),
                'curl_version' => curl_version()['version'],
                'ssl_version' => curl_version()['ssl_version']
            ];
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    });