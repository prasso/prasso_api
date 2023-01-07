<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class site_needs_dns extends Mailable
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
        Log::info('send admin mail to: '.json_encode($this->to));
        return $this->subject(config('constants.SITE_SETUP_EMAIL'))
                ->view('email.site_needs_dns')
                ->with('user_email',$this->user->email)
                ->with('dns', $this->dns);
            }
}
