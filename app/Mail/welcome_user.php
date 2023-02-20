<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

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
        $site=Controller::getClientFromHost();

        $subject = 'Welcome to '.$site->site_name;

        return $this->subject($subject)->view('email.welcome_user')
                    ->with('site',$site)->with('user_email',$this->user->email);
    }
}
