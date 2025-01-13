<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamInvitationController extends Controller
{
    /**
     * Redirect to dashboard after invitation acceptance
     *
     * @param  Request  $request
     * @param  int  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, $invitation)
    {
        return redirect(config('fortify.home'))->banner(
            __('Great! You have joined the team.'),
        );
    }
}
