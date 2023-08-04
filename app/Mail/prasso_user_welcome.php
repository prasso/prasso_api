<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class prasso_user_welcome extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $user;
    private $site;
    private $current_team_id;

    public function __construct($user,$site_team_id)
    {
        $this->user = $user;
        $this->current_team_id = $site_team_id;
        $this->site = Controller::getClientFromHost();

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('constants.PRASSO_USER_WELCOME_EMAIL'))
                ->view('email.prasso_user_welcome')
                ->with('user_email',$this->user->email)
                ->with('user_current_team_id', $this->user->current_team_id)
                ->with('site_name', $this->site->site_name); 
                
    }
}
