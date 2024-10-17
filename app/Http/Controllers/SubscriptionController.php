<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Auth;
use Laravel\Cashier\Cashier;
use App\Services\UserService;
use App\Services\AppsService;
use Laravel\Cashier\Payment;

use Illuminate\Support\Facades\Log;

class SubscriptionController extends BaseController
{
    protected $userService;
    protected $appsService;
 
    public function __construct(Request $request, UserService $userServ, AppsService $appsServ)
    {
        parent::__construct( $request);
        $this->userService = $userServ;
        $this->appsService = $appsServ;
    }

    public function showSubscriptionForm(Request $request)
    {
        // Get the current site (you might retrieve it differently, depending on your app's structure)
        $site = $this->site;

        // Retrieve the Stripe key from the Site model's Stripe relationship
        $stripeKey = $site->stripe_key; // Assuming the 'stripe' relationship returns the Stripe details

        // Generate a SetupIntent for the authenticated user using the site's Stripe configuration
        $setupIntent = $request->user()->createSetupIntent();

        // Retrieve subscription products from the ErpProduct model based on site_id and related product_type
        $productList = [
            'none' => 'Select a Plan'  // Add the default option at the start of the array
        ];

        // Get subscription products related to the site
        $subscriptionProducts = $site->erpProducts()
            ->whereHas('type', function ($query) {
                $query->where('product_type', 'Subscription');
            })
            ->get();

        // Map the retrieved products to the format ['id' => 'product_name']
        $productList += $subscriptionProducts->pluck('product_name', 'sku')->toArray();

        // Return the view with the setupIntent and products
        return view('subscription.form', [
            'setupIntent' => $setupIntent,
            'products' => $productList,
            'stripeKey' => $stripeKey, // Pass the Stripe key to the view for JavaScript initialization
        ]);
    }

    public function createSubscription(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Log the data to Laravel's log file for inspection
        info('Form data: ', $data);
        
        try {
            $paymentMethod = $request->input('payment_method');
            $subscriptionProduct = $request->input('subscription_product');

            $user->createOrGetStripeCustomer();

            $user->updateDefaultPaymentMethod($paymentMethod);

            $user->newSubscription('default', $subscriptionProduct)->create($paymentMethod);
            //add instructor level if product is subscription
            $this->userService->addOrUpdateSubscription($request, $user, $this->appsService, null); //set site to null, it is not necessary for this code to work

            $success['message'] = 'Subscription created successfully!';

            return $this->sendResponse($success, 'User subscribed.');
        } catch (\Exception $e) {
            info('failed with error: '.$e->getMessage());
            $this->adminNotifyOnError($e->getMessage());
            return $this->sendError('Subscription failed.',  $e->getMessage());   
        }
   
    }
  

    public function payment(Request $request)
    {
        $user = Auth::user();
        $paymentIntentId = $request->input('payment_intent_id');
        $product = $request->input('product');

        try {
            // Confirm the payment and associate it with the subscription or product.
            $payment = Payment::find($paymentIntentId);
            $payment->validate();

            // Handle logic for associating this payment with a product or subscription.

            return response()->json(['status' => 'Payment successful']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Payment failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function save_subscription(Request $request)
    {        
        $data = $request->all();
        
        info('save_subscription: '.json_encode($data));
        $user = Auth::user();

        $subject = 'Subscription Posted';
        $body = json_encode($data);
info('save_subscription: '.$body);

        $admin_user = \App\Models\User::where('email','bcp@faxt.com')->first();
        $admin_user -> sendContactFormEmail($subject, $body);

        $userresponse = $this->userService->addOrUpdateSubscription($request, $user, $this->appsService, $this->site);
        $body = 'returning this from save_subscription: '.json_encode($userresponse);

        $admin_user -> sendContactFormEmail($subject, $body);

        return $this->sendResponse($userresponse, 'ok');
    }

}
