<div>
 @if(Auth::user()->isSuperAdmin())
    <x-form-section submit="updateUserInformation">
        <x-slot name="title">
            {{ __('Profile Information') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update User') }}
        </x-slot>

        <x-slot name="form">
            <!-- Profile Photo -->
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">

                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $user->getProfilePhoto() }}" alt="{{ $user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>

            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="name" />
                <x-input-error for="name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="email" />
                <x-input-error for="email" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-4">
                <x-label for="phone" value="{{ __('Phone') }}" />
                <x-input id="phone" type="phone" class="mt-1 block w-full" wire:model.defer="phone" />
                <x-input-error for="phone" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>
@endif
    <x-form-section submit="UpdateSitesMember">
        <x-slot name="title">
            {{ __('Sites') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update sites membership of this user') }}
        </x-slot>

        <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">

                @foreach ($this->user_site_member_of as $id => $site_name)
                <div class="flex items-center justify-between">
                    <x-label value="{{ $site_name }}" />
                    @if (Auth::user()->getSiteCount() > 0 && Auth::user()->isThisSiteTeamOwner($id))
                    <x-responsive-nav-link href="{{ route('site.edit', $id) }}" :active="request()->routeIs('site.edit')" class="ml-auto">
                        <i class="material-icons">settings</i>
                    </x-responsive-nav-link>
                    @endif
                </div>
                @endforeach

            </div>        
                <x-label for="addSiteMember" value="Select a Site to add this user as team member" />
                <div class="flex items-center mt-2">
                    <select name="addSiteMember" id="addSiteMember" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="id_of_selected_site">
                    <option value="">-- Select --</option>
                    @foreach($site_selection as $id=>$site_name)
                        @if ($site_name != null)
                        <option value="{{ $id }}">{{ $site_name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <x-input-error for="addSiteMember" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="savedsite">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    <x-form-section submit="updateUserRoles">
        <x-slot name="title">
            {{ __('User Roles') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update the roles assigned to this user.') }}
        </x-slot>

        <x-slot name="form">
            <div class="col-span-6 sm:col-span-4">
                <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                    @foreach ($userRoles as $id => $role)
                    <div class="flex items-center justify-between">
                        <x-label value="{{ $role }}" />
                        <input type="checkbox" wire:model="selectedRoles" value="{{ $id }}" class="form-checkbox">
                    </div>
                    @endforeach
                </div>
                <div class="col-span-6 sm:col-span-4 mt-4">
                    <x-label for="addRole" value="Select a role to add to this user" />
                    <div class="flex items-center mt-2">
                        <select name="addRole" id="addRole" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="selectedRoleToAdd">
                            <option value="">-- Select --</option>
                            @foreach($allRoles as $id => $role)
                                <option value="{{ $id }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-input-error for="addRole" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>


    <x-form-section submit="UpdateTeamsOwned">
        <x-slot name="title">
            {{ __('Owned Teams') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update teams owned by this user') }}
        </x-slot>

        <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">

                @foreach ($teamsOwned  as $id => $name)
                <div class="flex items-center justify-between">
                    <x-label value="{{ $name }}" />
                    <x-responsive-nav-link href="{{ route('team.edit', $id) }}" :active="request()->routeIs('team.edit')" class="ml-auto">
                        <i class="material-icons">settings</i>
                    </x-responsive-nav-link>
                </div>
                @endforeach

            </div>
            <div class="col-span-6 sm:col-span-4">
                <x-label for="addOwnedTeam" value="Select a team to make this user the owner" />
                <div class="flex items-center mt-2">
                    <select name="addOwnedTeam" id="addOwnedTeam" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="id_of_owned_team">
                    <option value="">-- Select --</option>
                    @foreach($team_selection as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error for="addOwnedTeam" class="mt-2" />
            </div>
        </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="savedteam">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    @push('scripts')
    <script>
        window.livewire.on('saved', () => {
            Livewire.emit('saved');
        });
        window.livewire.on('savedsite', () => {
            Livewire.emit('saved site');
        });
        window.livewire.on('savedteam', () => {
            Livewire.emit('saved team');
        });
    </script>
@endpush
</div>