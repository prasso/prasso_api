<div>
    @if (Gate::check('addApp', $teamapp))
        <x-section-border />

        <!-- Add App -->
        <div class="mt-10 sm:mt-0">
            <x-form-section submit="addApp">
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
                        <x-label for="app_name" value="{{ __('App Name') }}" />
                        <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="addAppForm.app_name" />
                        <x-input-error for="app_name" class="mt-2" />
                    </div>

                    
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="mr-3" on="saved">
                        {{ __('Added.') }}
                    </x-action-message>

                    <x-button>
                        {{ __('Add') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    @endif

    @if ($team->apps->isNotEmpty())
        <x-section-border />

        <!-- Manage Apps -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
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
            </x-action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-dialog-modal wire:model="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
                <div class="mt-1 border border-gray-200 rounded-lg cursor-pointer">
                    
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

    <!-- Leave app Confirmation Modal -->
    <x-confirmation-modal wire:model="confirmingLeavingapp">
        <x-slot name="title">
            {{ __('Leave app') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to leave this app?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLeavingapp')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="leaveapp" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Remove App Confirmation Modal -->
    <x-confirmation-modal wire:model="confirmingAppRemoval">
        <x-slot name="title">
            {{ __('Remove App') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this person from the app?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingAppRemoval')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="removeApp" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
