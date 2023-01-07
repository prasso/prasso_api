<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class prasso_user_welcome extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
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
        Log::info('send user notification mail to: '.json_encode($this->to));
        return $this->subject(config('constants.PRASSO_USER_WELCOME_EMAIL'))
                ->view('email.prasso_user_welcome')
                ->with('user_email',$this->user->email)
                ->with('user_current_team_id', 1); // this is me for now. will change to the current coach
    }
}
