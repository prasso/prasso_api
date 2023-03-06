<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class livestream_notification extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $formsubject;
    private $formbody;
    private $fromemail;
    private $fromname;

    public function __construct($user,$formsubject,$formbody,$fromemail,$fromname)
    {

        //info('in livestream_notification: '.$formsubject.'  '.$formbody);
        $this->user = $user;
        $this->formsubject = $formsubject;
        $this->formbody = $formbody;
        $this->fromemail = $fromemail;
        $this->fromname = $fromname;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->formsubject)->view('email.livestream_notification')
        ->with('formbody',$this->formbody)
        ->with('user_email',$this->user->email)
        ->with('email_to_name',$this->user->name)
        ->with('email_from_name',$this->fromname)
        ->with('email_from',$this->fromemail);
    }
}
