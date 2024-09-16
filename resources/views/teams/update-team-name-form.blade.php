<x-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('Team Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s name and owner information.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="col-span-6">
            <x-label value="{{ __('Team Owner') }}" />

            <x-nav-link class="float-right" href="/team/{{$team->id}}/messages" :active="request()->routeIs('team.getmessages')">
                {{ __('Team Communications') }}
            </x-nav-link>

            <div class="flex items-center mt-2">
                <img class="w-12 h-12 rounded-full object-cover" src="{{ $team->owner->getProfilePhoto() }}" alt="{{ $team->owner->name }}">

                <div class="ml-4 leading-tight">
                    <div>{{ $team->owner->name }} </div>
                    <div class="text-gray-700 text-sm">{{ $team->owner->email }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Team Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Team Name') }}" />

            <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" :disabled="! Gate::check('update', $team)" />

            <x-input-error for="name" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-4">
            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                Team Sites
            </div>
            <div class="w-1/2">
                @foreach ($team->site as $site)
                @if ($site->site != null)
                <div class="flex items-center justify-between">
                    <x-label value="{{ $site->site->site_name   }}" />
                    @if (Auth::user()->getSiteCount() > 0 && Auth::user()->isThisSiteTeamOwner($site->site->id))
                    <x-responsive-nav-link href="{{ route('site.edit', $site->site->id) }}" :active="request()->routeIs('sites.edit')" class="ml-auto">
                        <i class="material-icons">settings</i>
                    </x-responsive-nav-link>
                    @endif
                </div>
            @endif
            @endforeach

            </div>
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
    @endif
</x-form-section>