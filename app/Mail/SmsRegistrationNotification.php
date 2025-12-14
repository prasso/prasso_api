<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Prasso\Messaging\Models\MsgTeamSetting;

class SmsRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $teamSetting;
    public $teamName;

    public function __construct(MsgTeamSetting $teamSetting, string $teamName)
    {
        $this->teamSetting = $teamSetting;
        $this->teamName = $teamName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New SMS Registration Request - ' . $this->teamName)
            ->view('email.sms-registration-notification')
            ->with([
                'teamSetting' => $this->teamSetting,
                'teamName' => $this->teamName,
            ]);
    }
}
