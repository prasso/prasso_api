<?php
namespace App\Livewire\Profile;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Site;
use App\Models\Team;
use App\Models\TeamSite;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateUserInformationForm extends Component
{
    public $id_of_owned_team;
    public $id_of_member_team;
    public $id_of_selected_site;
    public $selected_role;
    // these lists will not persist to the front end after the initialization of the view as they are objects
    public $team_selection;
    public $site_selection;
    public $role_selection;
    public $user_site_member_of;
    public $user_team_owner_of;

    public $user;
    public $userid ;
    public $name;
    public $email;
    public $password;
    public $profile_photo_path;
    public $firebase_uid;
    public $version;
    public $phone;
    

    public $photo;

    public function mount(User $user)
    {
        $this->userid = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = $user->password;
        $this->profile_photo_path = $user->profile_photo_path;
        $this->firebase_uid = $user->firebase_uid;
        $this->version = $user->version;
        $this->phone = $user->phone;
        
    
        
    }
    public function render()
    {
        $user = User::find($this->userid);
        
        $this->team_selection = Team::pluck('name','id');
        $this->site_selection = Site::orderBy('site_name')->pluck('site_name','id');
        $this->role_selection = ['admin'=>'admin','user'=>'user'];
        $this->updateUserMembershipLists($user);

    return view('livewire.profile.update-user-information-form', [
        'teamsOwned' => $this->user_team_owner_of,
        'sitesMemberOf' => $this->user_site_member_of,
    ]);
}
 
    public function updateUserInformation()
    {
        // Update the user's User information
        $user = User::find($this->userid);
        $user->phone = $this->phone;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        $this->emit('saved');

    }

    public function UpdateTeamsOwned(){
        
        //update teams set user_id = userid where id = id_of_owned_team
        DB::table('teams')
            ->where('id', $this->id_of_owned_team)
            ->update(['user_id' => $this->userid]);
       $this->user->refresh();
       $this->emit('savedteam');
    }
    protected $listeners = [
        'savedteam' => 'refreshTeamsOwned',
        'savedsite' => 'refreshSitesMemberOf',
    ];
    
    public function UpdateSitesMember(){
        info('UpdateSitesMember: '.$this->id_of_selected_site);
        // Refresh the user model to ensure that it has the latest data.
        $this->user->refresh();

        // Find the TeamSite model with the specified site ID and eager load the associated team.
        $teamSite = TeamSite::where('site_id', $this->id_of_selected_site)->with('team')->first();

        // If the TeamSite model doesn't exist, create a new one.
        if ($teamSite == null) {
            $teamSite = new TeamSite();
            $teamSite->site_id = $this->id_of_selected_site;
        }

        // If the team ID field of the TeamSite model is null, create a new team.
        if ($teamSite->team_id == null) {
            $team = new Team();
            $team->user_id = Auth::user()->id;
            $team->name = Site::find($this->id_of_selected_site)->site_name;
            $team->personal_team = false;
            $team->phone = ' ';
            $team->save();

            $teamSite->team_id = $team->id;
            $teamSite->save();
        }

        // Create a new team member for the team.
        $teamSite->team->team_members()->create([
            'user_id' => $this->userid,
            'role' => config('constants.TEAM_USER_ROLE'),
        ]);

        // Refresh the user model again to ensure that it has the latest data.
        $this->user->refresh();
        $this->emit('savedsite');
    }



    private function updateUserMembershipLists($user){
        $this->updateSiteMembership($user);
        $this->updateTeamOwnership($user);
    }
    private function updateSiteMembership($user){
        $this->user_site_member_of = Site::whereHas('teams', function ($query) use ($user) {
                $query->whereHas('team_members', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
        })->pluck('site_name','id');
    }
    private function updateTeamOwnership($user){
        $this->user_team_owner_of = Team::where('user_id',$this->user->id)->pluck('name','id');
    }

    public function refreshTeamsOwned()
    {
        $user = User::find($this->userid);
        $this->updateTeamOwnership($user);
    }

    public function refreshSitesMemberOf()
    {
        $user = User::find($this->userid);
        $this->updateSiteMembership($user);
    }
}