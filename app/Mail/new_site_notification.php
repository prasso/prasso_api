<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class new_site_notification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $user;
    private $dns;

    public function __construct($object)
    {
        $this->user = $object->current_user;
        $this->dns = $object->host;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('constants.SITE_SETUP_EMAIL'))
                ->view('email.new_site_notification')
                ->with('user_current_team_id',$this->user->current_team_id)
                ->with('user_email',$this->user->email)
                ->with('dns', $this->dns);
            }
}
