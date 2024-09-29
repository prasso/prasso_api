<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class admin_error_notification extends Mailable
{
    use Queueable, SerializesModels;

    public $error_message;

    public function __construct($message)
    {
        info('error: '.$message);
        $this->error_message = $message;
    }

    public function build()
    {
        return $this->subject(config('constants.ADMIN_ERROR_EMAIL'))
                    ->view('email.admin_error_notification')
                    ->with([
                        'error_message' => $this->error_message,
                    ]);
    }
}

