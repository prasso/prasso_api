<div class="shadow overflow-hidden sm:rounded-md">
    @if ( Auth::user() != null)
    <div class="p-12 bg-white col-span-12 items-center ">
        <div class="p-0 m-auto">
            <div class="flex px-4">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full" src="{{ Auth::user()->getProfilePhoto() }}" alt="{{ Auth::user()->name }}" />
                </div>

                <div class="ml-3">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            @if ( !Auth::user()->isInstructor($site))
                {!! $user_content !!}
            @else
                @php
                    $isPrassoSite = \App\Models\Site::isPrasso(parse_url(url()->current(), PHP_URL_HOST));
                    $isSuperAdmin = Auth::user()->isSuperAdmin();
                    $isInstructorWithNoSites = Auth::user()->isInstructor() && Auth::user()->getSiteCount() == 0;
                @endphp

                @if ($isPrassoSite && ($isSuperAdmin || $isInstructorWithNoSites))
                <!-- New Site and App button removed -->
                @else
                @if (Auth::user()->isInstructor($site) && Auth::user()->getSiteCount() > 0)
                <div class="max-w-xs m-auto">
                <div class="block py-2 text-lg font-semibold text-gray-600">
                                {{ __('Admin') }}
                            </div>
                    <!-- a link to this user's site dashboard -->
                    <x-responsive-nav-link href="/site/{{  Auth::user()->getUserOwnerSiteId()  }}/edit">
                        <div class="mt-3 space-y-1">
                            <div class="font-sanstext-gray-600">
                                {{ __('My Site Dashboard') }}
                            </div>
                        </div>
                    </x-responsive-nav-link>

                </div>
                @endif
                @endif

                {!! $user_content !!}
            @endif
            <div class="grid max-w-xs m-auto">
                <div class="row">

                    <div class="col-6">
                        <div class="mt-3 space-y-1">

                        <div class="border-t border-gray-200 mt-2"></div>
                            <!-- Account Management -->
                            <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                                {{ __('Profile') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link href="{{ route('filament.site-admin.pages.compose-and-send-message') }}">
                                {{ __('Messaging') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link href="{{ route('subscription.form') }}">
                                {{ __('Subscribe') }}
                            </x-responsive-nav-link>
                            <div class="border-t border-gray-200 mt-2"></div>
                        </div>
                        @if (Auth::user()->isSuperAdmin())
                        <div class="font-sans block px-4 py-2 text-lg font-semibold text-gray-600">
                            Manage Apps and Sites   
                        </div>

                        <x-responsive-nav-link href="{{ route('apps.show', Auth::user()->current_team_id)  }}">
                            {{ __('Apps') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('sites.show')  }}">
                            {{ __('Sites') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('admin.site-packages.manage') }}" class="ml-4">
                            {{ __('Site Packages') }}
                        </x-responsive-nav-link>

                        @endif
                        @if (Auth::user()->isInstructor($site) )
                        <!-- Team Management -->
                        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="border-t border-gray-200 mt-2"></div>

                        <div class="border-t border-gray-200"></div>
                        <div class="block py-2 text-lg font-semibold text-gray-600">
                            {{ __('Add to Image Library') }}
            
                            @include('partials._image-upload-styles')
                            @include('partials._image-upload', ['site_id' => $site->id])
                            </div>
                        </div>
                        @endif

                        <div class="border-t border-gray-200"></div>
                        @if (Auth::user()->isSuperAdmin())
                            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                                Manage Teams
                            </div>
                            @foreach (\App\Models\Team::all() as $team)
                                <div class="flex items-center justify-between">
                                    <x-switchable-team :team="$team" component="responsive-nav-link" />
                                    @if (Auth::user()->getSiteCount() > 0 && Auth::user()->canManageTeamForSite($team->id))
                                        <!-- Team Settings -->
                                        <x-responsive-nav-link href="{{ route('teams.show', $team->id) }}" :active="request()->routeIs('teams.show')" class="ml-auto">
                                        <i class="material-icons">settings</i>
                                        </x-responsive-nav-link>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            @if (count(Auth::user()->allTeams()) > 1)
                            <!-- Team Switcher -->
                            <div class="block py-2 text-lg font-semibold text-gray-600">
                                My Teams
                            </div>

                            @foreach (Auth::user()->allTeams() as $team)
                                <div class="flex items-center justify-between">
                                    <x-switchable-team :team="$team" component="responsive-nav-link" />
                                    @if (Auth::user()->getSiteCount() > 0 && Auth::user()->canManageTeamForSite($team->id))
                                        <!-- Team Settings -->
                                        <x-responsive-nav-link href="{{ route('teams.show', $team->id) }}" :active="request()->routeIs('teams.show')" class="ml-auto">
                                        <i class="material-icons">settings</i>
                                        </x-responsive-nav-link>
                                    @endif
                                </div>
                            @endforeach
                            @endif
                        @endif

                        @if ((\App\Models\Site::isPrasso(parse_url(url()->current(), PHP_URL_HOST)) && Auth::user()->isSuperAdmin()) || (Auth::user()->isInstructor($site) && Auth::user()->isThisSiteTeamOwner($site->id)))
               
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>

                        @endif
                        @endif

                        <div class="m-auto mt-2">
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-responsive-nav-link class="teambutton px-4 py-2 font-semibold text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl" href="{{ route('logout') }}" onclick="event.preventDefault();
                                this.closest('form').submit();">
                                    {{ __('Logout') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- no login cookie -->
        <x-welcome></x-welcome>
        @endif

        @push('scripts')
            <script src="{{ asset('js/image-upload.js') }}"></script>
        @endpush
    </div>