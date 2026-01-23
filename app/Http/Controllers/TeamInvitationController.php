<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Actions\Jetstream\AcceptInvitation;
use Illuminate\Http\Request;

class TeamInvitationController extends Controller
{
    /**
     * Accept a team invitation using custom AcceptInvitation action
     *
     * @param  Request  $request
     * @param  int  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, $invitation)
    {
        $model = new \Laravel\Jetstream\Jetstream;
        $invitationModel = $model->teamInvitationModel()::whereKey($invitation)->firstOrFail();

        $user = $request->user();
        
        // Use the custom AcceptInvitation action
        app(AcceptInvitation::class)->accept($user, $invitationModel);

        return redirect(config('fortify.home'))->banner(
            __('Great! You have joined the team.'),
        );
    }
}
