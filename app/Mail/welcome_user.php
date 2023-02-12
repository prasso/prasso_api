<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Site;

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
        $host = request()->getHttpHost();
        $site = Site::getClient($host);
        if ($site == null)
        {
            $site = Site::getClient( 'prasso.io');
        }

        $subject = 'Welcome to '.$site->site_name;

        return $this->subject($subject)->view('email.welcome_user')
                    ->with('site',$site)->with('user_email',$this->user->email);
    }
}
