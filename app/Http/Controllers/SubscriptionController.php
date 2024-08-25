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

        $this->middleware('instructorusergroup');
        $this->userService = $userServ;
        $this->appsService = $appsServ;
    }
    
    /*public function showSubscriptionForm()
    {
        $user = Auth::user();

        // Ensure the user is a Stripe customer
        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

    // Create a PaymentIntent with a specific amount
    $intent = Cashier::stripe()->paymentIntents->create([
        'amount' => 1000, // Example amount in cents (adjust as necessary)
        'currency' => 'usd',
        'customer' => $user->stripe_id,
    ]);

    
        return view('subscription.form', compact('intent'));
    }*/

    public function showSubscriptionForm(Request $request)
{
    // Generate a SetupIntent for the authenticated user
    $setupIntent = $request->user()->createSetupIntent();
    
    // Get the list of Stripe products/plans (this could be fetched from your database or an external source)
    $products = [
        'none' => 'Select a Plan',
        config('constants.STRIPE_MONTHLY_HOSTING_SMALL_BUSINESS_PRICE') => 'Small Business Monthly Hosting',
        config('constants.STRIPE_DEVELOPER_10_HR_MO_PRICE') => 'Developer Support (Tier 1)',

    ];

    // Return the view with the setupIntent and products
    return view('subscription.form', [
        'setupIntent' => $setupIntent,
        'products' => $products
    ]);
}

    public function createSubscription(Request $request)
    {
        info('in subscribe');
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
info('succeeded');
           // return redirect('/dashboard')->with('message', 'Subscription created successfully!'); 
            $success['message'] = 'Subscription created successfully!';

            return $this->sendResponse($success, 'User subscribed.');
        } catch (\Exception $e) {
            info('failed with error: '.$e->getMessage());
            $this->adminNotifyOnError($e->getMessage());
           // return response()->json(['status' => 'Subscription failed', 'error' => $e->getMessage()], 500);
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
