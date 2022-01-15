<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class contact_form extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $formsubject;
    private $formbody;

    public function __construct($user,$formsubject,$formbody)
    {

        //info('in contact_form: '.$formsubject.'  '.$formbody);
        $this->user = $user;
        $this->formsubject = $formsubject;
        $this->formbody = $formbody;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('constants.CONTACT_FORM_SUBJECT'))->view('email.contact_form')
        ->with('formsubject',$this->formsubject)
        ->with('formbody',$this->formbody)
        ->with('user_email',$this->user->email);
    }
}
