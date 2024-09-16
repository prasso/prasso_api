<?php
namespace App\Livewire\Profile;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Site;
use App\Models\Team;
use App\Models\Role;
use App\Models\TeamSite;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\UserService;

class UpdateUserInformationForm extends Component
{
    public $id_of_owned_team;
    public $id_of_member_team;
    public $id_of_selected_site;
    public $selected_role;
    // these lists will not persist to the front end after the initialization of the view as they are objects
    public $team_selection;
    public $site_selection;
    
    public $userRoles = [];
    public $allRoles = [];
    public $selectedRoles = [];
    public $selectedRoleToAdd;
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
        $this->userRoles = $user->roles->pluck('role_name', 'id')->toArray();
        $this->allRoles = Role::pluck('role_name', 'id')->toArray();
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        
    
        
    }
    public function render()
    {
        $user = User::find($this->userid);
        
        $this->team_selection = Team::pluck('name','id');
        $this->site_selection = Site::orderBy('site_name')->pluck('site_name','id');
        
        $this->userRoles = $user->roles->pluck('role_name', 'id')->toArray();
        $this->allRoles = Role::pluck('role_name', 'id')->toArray();
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->updateUserMembershipLists($user);

    return view('livewire.profile.update-user-information-form', [
        'teamsOwned' => $this->user_team_owner_of,
        'sitesMemberOf' => $this->user_site_member_of,
        'userRoles' => $this->userRoles
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

       $this->dispatch('saved');

    }

    public function updateUserRoles()
    {
        $this->validate([
            'selectedRoles' => 'array',
            'selectedRoleToAdd' => 'nullable|exists:roles,id',
        ]);

        $user = User::find($this->userid);
        $user->roles()->sync($this->selectedRoles);

        if ($this->selectedRoleToAdd) {
            $user->roles()->attach($this->selectedRoleToAdd);
        }

        $this->dispatch('saved');
    }

    public function UpdateTeamsOwned(){
        
        //update teams set user_id = userid where id = id_of_owned_team
        DB::table('teams')
            ->where('id', $this->id_of_owned_team)
            ->update(['user_id' => $this->userid]);
       $this->user->refresh();
      $this->dispatch('savedteam');
    }
    protected $listeners = [
        'savedteam' => 'refreshTeamsOwned',
        'savedsite' => 'refreshSitesMemberOf',
    ];
    
    public function UpdateSitesMember(){
        info('UpdateSitesMember: '.$this->id_of_selected_site);
        // Resolving the service class from the service container
        $userService = app(UserService::class);
        $this->user = User::find($this->userid);
        $userService->UpdateSitesMember($this->user,null, $this->id_of_selected_site);

        // Refresh the user model again to ensure that it has the latest data.
        $this->user->refresh();
       $this->dispatch('savedsite');
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