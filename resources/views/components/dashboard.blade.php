<div class="shadow overflow-hidden sm:rounded-md">
    @if ( Auth::user() != null)
    <div class="p-12 bg-white col-span-12 items-center ">
        <div class="max-w-xs p-0 m-auto">
            <div class="flex px-4">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full" src="{{ Auth::user()->getProfilePhoto() }}" alt="{{ Auth::user()->name }}" />
                </div>

                <div class="ml-3">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
           
            @if (Auth::user()->isSuperAdmin() || Auth::user()->getSiteCount() == 0)
                <x-jet-responsive-nav-link href="{{ route('apps.newsiteandapp', Auth::user()->current_team_id)  }}">
                    <div class="text-center bg-gray-50 border-2 border-indigo-600/100">
                    <div class="font-sans  text-lg font-semibold text-gray-600">
                        {{ __('New Site and App') }}
                    </div>
                </div>
                </x-jet-responsive-nav-link>
            @else
                <!-- a link to this user's site dashboard -->
                <x-jet-responsive-nav-link href="{{  Auth::user()->getUserSiteUrl()  }}">
                    <div class="mt-3 space-y-1">
                    <div class="font-sanstext-gray-600">
                        {{ __('My Site Dashboard') }}
                    </div>
                    </div>
                </x-jet-responsive-nav-link>
            @endif
            
            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-jet-responsive-nav-link>
                <div class="border-t border-gray-200 mt-2"></div>
</div>
                @if (Auth::user()->isSuperAdmin())
                <div class="font-sans block px-4 py-2 text-lg font-semibold text-gray-600">
                    {{ __('Manage Apps') }}
                </div>

                <x-jet-responsive-nav-link href="{{ route('apps.show', Auth::user()->current_team_id)  }}">
                    {{ __('Apps') }}
                </x-jet-responsive-nav-link>

                <x-jet-responsive-nav-link href="{{ route('sites.show', Auth::user()->allTeams()->first()->id)  }}">
                    {{ __('Sites') }}
                </x-jet-responsive-nav-link>

                @endif
                @if (Auth::user()->isInstructor())
                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <div class="border-t border-gray-200 mt-2"></div>

                <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                    {{ __('Manage Team') }}
                </div>

                <!-- Team Settings -->
                <x-jet-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                    {{ __('Team Settings') }}
                </x-jet-responsive-nav-link>

                <x-jet-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                    {{ __('Create New Team') }}
                </x-jet-responsive-nav-link>

                <div class="border-t border-gray-200"></div>
                    <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                        {{ __('Add to Image Library') }}
                    <div class="px-4 py-2">
                        <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" type="file" name="image">
                            <button class="teambutton cursor-pointer mt-2 border p-2 rounded-xl px-2 py-1  text-sm  text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl" type="submit">Upload</button>
                        </form>
                    </div>
                </div>
                @endif

                <div class="border-t border-gray-200"></div>

                @if (count(Auth::user()->allTeams()) > 1)
                <!-- Team Switcher -->
                <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                    {{ __('Switch Teams') }}
                </div>

                @foreach (Auth::user()->allTeams() as $team)
                <x-jet-switchable-team :team="$team" component="jet-responsive-nav-link" />
                @endforeach
                @endif
                @endif

                <div class="m-auto mt-2">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-jet-responsive-nav-link class="teambutton px-4 py-2 font-semibold text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl" href="{{ route('logout') }}" onclick="event.preventDefault();
                                    this.closest('form').submit();">
                            {{ __('Logout') }}
                        </x-jet-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- no login cookie -->
    <x-welcome></x-welcome>
    @endif
</div>