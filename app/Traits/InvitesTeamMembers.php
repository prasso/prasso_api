<?php

namespace App\Traits;

use App\Notifications\InviteTeamMember;
use App\Notifications\InviteNewsletterSubscriber;

trait InvitesTeamMembers {
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailInviteNotification() {
        if ($this->role == config('constants.NEWSLETTER_ROLE_TEXT'))
        {
            $this->notify(new InviteNewsletterSubscriber);
        }
        else
        {
            $this->notify(new InviteTeamMember);
        }
    }
}
