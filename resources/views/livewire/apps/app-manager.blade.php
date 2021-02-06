<div>
    @if (Gate::check('addApp', $teamapp))
        <x-jet-section-border />

        <!-- Add App -->
        <div class="mt-10 sm:mt-0">
            <x-jet-form-section submit="addApp">
                <x-slot name="title">
                    {{ __('Add App') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Add a new App to your team.') }}
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600">
                            {{ __('Please provide the name of the app you would like to add to this team.') }}
                        </div>
                    </div>

                    <!-- App Name -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="app_name" value="{{ __('App Name') }}" />
                        <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="addAppForm.app_name" />
                        <x-jet-input-error for="app_name" class="mt-2" />
                    </div>

                    
                </x-slot>

                <x-slot name="actions">
                    <x-jet-action-message class="mr-3" on="saved">
                        {{ __('Added.') }}
                    </x-jet-action-message>

                    <x-jet-button>
                        {{ __('Add') }}
                    </x-jet-button>
                </x-slot>
            </x-jet-form-section>
        </div>
    @endif

    @if ($team->apps->isNotEmpty())
        <x-jet-section-border />

        <!-- Manage Apps -->
        <div class="mt-10 sm:mt-0">
            <x-jet-action-section>
                <x-slot name="title">
                    {{ __('Apps') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('All of the people that are part of this app.') }}
                </x-slot>

                <!-- App List -->
                <x-slot name="content">
                    <div class="space-y-6">
                        @foreach ($team->apps->sortBy('app_name') as $teamapp)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="w-8 h-8 rounded-full" src="{{ $team->appicon }}" alt="{{ $team->name }}">
                                    <div class="ml-4">{{ $teamapp->app_name }}</div>
                                </div>

                                <div class="flex items-center">
                                   

                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-jet-action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-jet-dialog-modal wire:model="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
                <div class="mt-1 border border-gray-200 rounded-lg cursor-pointer">
                    
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Leave app Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingLeavingapp">
        <x-slot name="title">
            {{ __('Leave app') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to leave this app?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingLeavingapp')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="leaveapp" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>

    <!-- Remove App Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingAppRemoval">
        <x-slot name="title">
            {{ __('Remove App') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this person from the app?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingAppRemoval')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="removeApp" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>
</div>
