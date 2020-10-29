<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
        // curl_exec($ch);
        $respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ranit = curl_exec($ch);
 
        $resp = json_decode( $ranit, true);
        curl_close($ch);
        Log::info($resp);

        return redirect('/contact')->with('message', 'Your message was sent.'); 
    }

}