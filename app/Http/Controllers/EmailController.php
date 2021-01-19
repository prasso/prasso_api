<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class EmailController extends Controller
{
    protected $serverKey;
    protected $user_device_token;
 
    public function __construct()
    {
        $this->serverKey = config('app.firebase_server_key');
        $this->user_device_token = config('app.contact_device_token');
    }

    public function sendEmail(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'subject' => 'required',
            'body' => 'required',
        ]);

        $emails = $request->email;
        $subject = $request->subject;
        $body = $request->body;


        $data = [
            "to" => $this->user_device_token,
            "notification" =>
                [
                    "title" => 'Prasso Contact Request:'. $subject,
                    "body" => $body,
                    "icon" => url('/logo.png')
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

        return redirect('/contact')->with('message', 'Your message was sent.'); 
    }

}