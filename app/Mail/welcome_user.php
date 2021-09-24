<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class welcome_user extends Mailable
{
    use Queueable, SerializesModels;

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //Log::info('send mail to: '.json_encode($this->to));
        return $this->subject(config('constants.WELCOME_EMAIL_SUBJECT'))->view('email.welcome_user')->with('user_email',$this->user->email);
    }
}
