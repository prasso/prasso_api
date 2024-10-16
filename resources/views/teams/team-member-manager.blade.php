<div>
   
    <x-section-border />

    <!-- Manage Team Members -->
    <div class="mt-10 sm:mt-0">
        <x-action-section>
            <x-slot name="title">
                {{ __('Team Members') }}
            </x-slot>

            <x-slot name="description">
                {{ __('All of the people that are part of this team.') }}
            </x-slot>

            <!-- Team Member List -->
            <x-slot name="content">
                <div class="space-y-6">
                    @foreach ($team->users->sortBy('name') as $user)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <img class="w-8 h-8 rounded-full" src="{{ $user->getProfilePhoto() }}" alt="{{ $user->name }}">
                            <div class="ml-4">{{ $user->name }}</div>
                        </div>

                        <div class="flex items-center">


                            <!-- Leave Team -->
                            @if ($this->user->id === $user->id)
                            <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none" wire:click="$toggle('confirmingLeavingTeam')">
                                {{ __('Leave') }}
                            </button>

                            <!-- Remove Team Member -->
                            @elseif (Gate::check('removeTeamMember', $team))
                            <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none" wire:click="confirmTeamMemberRemoval('{{ $user->id }}')">
                                {{ __('Remove') }}
                            </button>

                            <x-responsive-nav-link href="{{ route('profile.updateuser', $user->id) }}" :active="request()->routeIs('profile.updateuser')" class="ml-auto">
                                <i class="material-icons">settings</i>
                            </x-responsive-nav-link>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot>
        </x-action-section>
    </div>


    @if (Gate::check('addTeamMember', $team))
    <x-section-border />

    <!-- Add Team Member -->
    <div class="mt-10 sm:mt-0">
        <x-form-section submit="addTeamMember">
            <x-slot name="title">
                {{ __('Add Team Member') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Add a new team member to your team, allowing them to collaborate with you.') }}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6">
                    <div class="max-w-xl text-sm text-gray-600">
                        {{ __('Please provide the email address of the person you would like to add to this team. The email address must be associated with an existing account.') }}
                    </div>
                </div>

                <!-- Member Email -->
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" type="text" class="mt-1 block w-full" wire:model.defer="addTeamMemberForm.email" />
                    <x-input-error for="email" class="mt-2" />
                </div>

                <!-- Role -->
                @if (Auth::user()->hasRole('1') || $site->getTeamOwner($site)))
                @if (count($this->roles) > 0)
                <div class="col-span-6 lg:col-span-4">
                    <x-label for="role" value="{{ __('Role') }}" />
                    <x-input-error for="role" class="mt-2" />

                    <div class="mt-1 border border-gray-200 rounded-lg cursor-pointer">
                        @foreach ($this->roles as $index => $role)

                        @if($role->name != 'Administrator')
                        <div class="px-4 py-3 {{ $index > 0 ? 'border-t border-gray-200' : '' }}" wire:click="$set('addTeamMemberForm.role', '{{ $role->key }}')">
                            <div class="{{ isset($addTeamMemberForm['role']) && $addTeamMemberForm['role'] !== $role->key ? 'opacity-50' : '' }}">
                                <!-- Role Name -->
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-600 {{ $addTeamMemberForm['role'] == $role->key ? 'font-semibold' : '' }}">
                                        {{ $role->name }}
                                    </div>

                                    @if ($addTeamMemberForm['role'] == $role->key)
                                    <svg class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @endif
                                </div>

                                <!-- Role Description -->
                                <div class="mt-2 text-xs text-gray-600">
                                    {{ $role->description }}
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach

                    </div>
                </div>
                @endif
                @else
                <x-input type="text" style="display:none" value="user" class="mt-1 block w-full" wire:model.defer="addTeamMemberForm.role" />


                @endif
            </x-slot>

            <x-slot name="actions">
                <x-action-message class="mr-3" on="saved">
                    {{ __('Invited.') }}
                </x-action-message>

                <x-button>
                    {{ __('Add') }}
                </x-button>
            </x-slot>
        </x-form-section>
    </div>
    @endif

    <!-- Role Management Modal -->
    <x-dialog-modal wire:model="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-1 border border-gray-200 rounded-lg cursor-pointer">
                @foreach ($this->roles as $index => $role)
                <div class="px-4 py-3 {{ $index > 0 ? 'border-t border-gray-200' : '' }}" wire:click="$set('currentRole', '{{ $role->key }}')">
                    <div class="{{ $currentRole !== $role->key ? 'opacity-50' : '' }}">
                        <!-- Role Name -->
                        <div class="flex items-center">
                            <div class="text-sm text-gray-600 {{ $currentRole == $role->key ? 'font-semibold' : '' }}">
                                {{ $role->name }}
                            </div>

                            @if ($currentRole == $role->key)
                            <svg class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                        </div>

                        <!-- Role Description -->
                        <div class="mt-2 text-xs text-gray-600">
                            {{ $role->description }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-secondary-button>

            <x-button class="ml-2" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Leave Team Confirmation Modal -->
    <x-confirmation-modal wire:model="confirmingLeavingTeam">
        <x-slot name="title">
            {{ __('Leave Team') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to leave this team?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLeavingTeam')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Remove Team Member Confirmation Modal -->
    <x-confirmation-modal wire:model="confirmingTeamMemberRemoval">
        <x-slot name="title">
            {{ __('Remove Team Member') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this person from the team?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingTeamMemberRemoval')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>