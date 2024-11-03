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

class StripeController extends BaseController
{
    protected $userService;
    protected $appsService;
 
    public function __construct(Request $request, UserService $userServ, AppsService $appsServ)
    {
        parent::__construct( $request);
        $this->userService = $userServ;
        $this->appsService = $appsServ;
    }

    public function showDonationForm(Request $request){
        // Get the current site 
        $site = $this->site;

        $this->setStripeApi($site);

        // Load donation items from the database
         $donationList = [
         
        ];

        // Get one-time products from the site's associated ERP products
        $donationProducts = $site->erpProducts()
            ->whereHas('type', function ($query) {
                $query->where('product_type', 'Donation');
            })
            ->get();

        // Map the products to ['sku' => 'product_name'] for use in a dropdown
        $donationList += $donationProducts->pluck('product_name', 'sku')->toArray();

        // Generate a SetupIntent to confirm the card without charging
        $setupIntent = $request->user()->createSetupIntent();

        // Pass the donation items and setup intent to the form view
        return view('donate.form', [
            'setupIntent' => $setupIntent->client_secret,
            'donationItems' => $donationList,
            'stripeKey' => $site->stripe->key,
        ]);
    }
    

public function submitDonation(Request $request)
{
    $request->validate([
        'total_donation' => 'required|numeric|min:1',
    ]);

     // Get the current site 
     $site = $this->site;

     $this->setStripeApi($site);

    // Create the PaymentIntent with the total donation amount
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $request->input('total_donation') * 100, // Amount in cents
        'currency' => 'usd',
        'payment_method_types' => ['card'],
        'metadata' => [
            'user_id' => $request->user()->id,
            'donation_items' => json_encode($request->input('donation_items', [])), // Save selected items for reference
        ]
    ]);

    return response()->json([
        'client_secret' => $paymentIntent->client_secret,
    ]);
}


    public function showCheckoutForm(Request $request)
    {
        // Get the current site 
        $site = $this->site;
        $this->setStripeApi($site);
       
        // Load one-time products related to the site
        $productList = [
            'none' => 'Select a Product'  // Default option for the dropdown
        ];

        // Get one-time products from the site's associated ERP products
        $oneTimeProducts = $site->erpProducts()
            ->whereHas('type', function ($query) {
                $query->where('product_type', 'Single');
            })
            ->get();

        // Map the products to ['sku' => 'product_name'] for use in a dropdown
        $productList += $oneTimeProducts->pluck('product_name', 'sku')->toArray();

        // // Create a Stripe PaymentIntent for a one-time payment
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => 50, // This will be dynamically updated based on the selected product
            'currency' => 'usd', // Set your currency
            'metadata' => [
                'site_id' => $site->id,
                'user_id' => $request->user()->id,
            ]
        ]);

        // Pass required data to the view
        return view('checkout.form', [
            'paymentIntent' => $paymentIntent->client_secret,
            'products' => $productList,
            'stripeKey' => $site->stripe->key,
        ]);
    
    }


    public function createPaymentIntent(Request $request)
    {
            // Get the current site 
        $site = $this->site;

        $product = $site->erpProducts()->where('sku', $request->productId)->first();

    if (!$product) {
        return response()->json(['error' => 'Product not found.'], 404);
    }

    $this->setStripeApi($site);

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $product->price * 100, // Stripe amount is in cents
        'currency' => 'usd',
        'metadata' => [
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
        ]
    ]);

    return response()->json([
        'client_secret' => $paymentIntent->client_secret
    ]);
    }

    public function payment(Request $request)
    {
        $user = Auth::user();

        $this->setStripeApi($this->site);
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


    public function showSubscriptionForm(Request $request)
    {
        
        // Get the current site (you might retrieve it differently, depending on your app's structure)
        $site = $this->site;

        $this->setStripeApi($site);

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
            'stripeKey' => $site->stripe->key, // Pass the Stripe key to the view for JavaScript initialization
        ]);
    
    
    }

    public function createSubscription(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        // Log the data to Laravel's log file for inspection
        info('Form data: ', $data);
        
        $this->setStripeApi($this->site);
        try {
            $paymentMethod = $request->input('payment_method');
            $subscriptionProduct = $request->input('subscription_product');

            $user->createOrGetStripeCustomer();

            $user->updateDefaultPaymentMethod($paymentMethod);

            $user->newSubscription('default', $subscriptionProduct)->create($paymentMethod);
            //add instructor level if product is subscription
            $this->save_subscription($request);
            $success['message'] = 'Subscription created successfully!';

            return $this->sendResponse($success, 'User subscribed.');
        } catch (\Exception $e) {
            info('failed with error: '.$e->getMessage());
            $this->adminNotifyOnError($e->getMessage());
            return $this->sendError('Subscription failed.',  $e->getMessage());   
        }
   
    }
  


    public function save_subscription(Request $request)
    {        
        $data = $request->all();
        
        $user = Auth::user();

        $subject = 'Subscription Posted';
        $body = json_encode($data);
        info('save_subscription: '.$body);

        $admin_user = $this->site->superAdmin();
        $admin_user -> sendContactFormEmail($subject, $body);

        $userresponse = $this->userService->addOrUpdateSubscription($request, $user, $this->appsService, $this->site);

        return $this->sendResponse($userresponse, 'ok');
    }

    private function setStripeApi($site){
        // Retrieve the Stripe public key from the Site model's Stripe relationship
        $stripeSecret = $site->stripe->secret; 
        // Set the Stripe API key
        \Stripe\Stripe::setApiKey($stripeSecret);
    }


}
