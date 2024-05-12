<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Site;
use App\Services\UserService;


class EmailController extends BaseController
{
    protected $serverKey;
    protected $userService;
 
    public function __construct(Request $request, UserService $userServ )
    {
        parent::__construct( $request);
        $this->serverKey = config('app.firebase_server_key');
        $this->userService = $userServ;
    }

    public function unsubscribe(Request $request)
    {
        $this->userService->unsubscribe($request['email']);
        return redirect('/page/email_subscription_removed')->with('message', 'You are unsubscribed. :-('); 
    
    }

    public function confirm_newsletter_subscription(Request $request)
    {
        $this->userService->confirmNewsletter($request['email']);

        return redirect('/page/email_subscription_confirmed')->with('message', 'Please confirm your subscription'); 
    }

    public function registerEmailForNewsletter(Request $request)
    {
        if (!isset($request['email']) )
        {
            return redirect('/')->with('message','email was empty');
        }
        $email = $request['email'];
        // Remove all illegal characters from email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Validate e-mail
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //send a confirmation email to the poster - if they confirm to signup for the newsletter then inform me
            $this->userService->subscribeNewsletter($request['email']);

            return redirect('/page/please_confirm_your_subscription')->with('message', 'Please confirm your subscription'); 
    
        } else {
            return redirect('/')->with('message','email was not valid');
        }
   }

   // this method sends to the main admin only. it sends the info entered. no other emails are sent from here
    public function sendEmail(Request $request, Site $site) {
        $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $emails = $request->email;
        $subject = $request->subject;
        $body = $request->body.' - from '.$emails;

        $admin_user = \App\Models\User::where('email','bcp@faxt.com')->first();
        $admin_user -> sendContactFormEmail($subject, $body);

       /* /// txt message only works if the app is in the background of the device - at least on Android
        $data = [
            "to" => $admin_user->pn_token,
            "notification" =>
                [
                    "title" => 'Prasso Contact Request:'. $subject,
                    "body" => $body,
                    "icon" => url($site->logo_image)
                ],
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json',
        ];
   
        $url='https://fcm.googleapis.com/fcm/send';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> 'key='. $this->serverKey,
        ])->post($url, $data);
        */
        return redirect('/dashboard')->with('message', 'Your message was sent.'); 
    }

}