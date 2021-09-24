<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class user_needs_coach extends Mailable
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
        Log::info('send coach mail to: '.json_encode($this->to));
        return $this->subject(config('constants.COACH_COPY_WELCOME_EMAIL'))
                ->view('email.user_needs_coach')
                ->with('user_email',$this->user->email)
                ->with('user_current_team_id', 1); // this is me for now. will change to the current coach
    }
}
