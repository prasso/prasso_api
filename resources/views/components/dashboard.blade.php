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
            @if (!Auth::user()->isSuperAdmin() && !Auth::user()->isInstructor())
            <div class="max-w-md m-auto">
                <div class="font-sans px-4 py-2">
                <h2 class="text-lg font-semibold text-gray-600 mb-4">Welcome to Prasso!</h2>
                <ul class="list-disc list-inside font-medium text-sm text-gray-500">
                    <li>We will reach out to the registered email with next steps.</li>
                </ul>
                <p class="mt-4 text-lg font-semibold text-gray-600 mb-4">
                    Feel free to reach out. We're here to make the process smooth and enjoyable.
                </p>

                <!-- Special Subscribe Link -->
                <div class="text-center mt-6">
                    <a href="/subscribe" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-2 px-6 rounded-full shadow-lg hover:from-purple-600 hover:to-blue-500 transform hover:scale-105 transition duration-300 ease-in-out">
                    Subscribe Now
                    </a>
                </div>
                </div>
            </div>
           @else
                @if ((\App\Models\Site::isPrasso(parse_url(url()->current(), PHP_URL_HOST)) && Auth::user()->isSuperAdmin()) || (Auth::user()->isInstructor() && Auth::user()->getSiteCount() == 0))
                <div class="max-w-md m-auto">
                    <x-responsive-nav-link href="{{ route('apps.newsiteandapp', Auth::user()->current_team_id)  }}">
                        <div class="text-center bg-gray-50 border-2 border-indigo-600/100">
                            <div class="font-sans  text-lg font-semibold text-gray-600">
                                New Site and App
                            </div>
                        </div>
                    </x-responsive-nav-link>
                    @if (!Auth::user()->isSuperAdmin())
                    <div class="font-sans px-4 py-2">
                        <h2 class="text-lg font-semibold text-gray-600 mb-4">Welcome to Prasso! We're excited for you to start building your apps. Here's how to get up and running:</h2>
                        <ul class="list-disc list-inside font-medium text-sm text-gray-500">
                            <li>Create Your Site: The first step is using the site creation wizard to make your site. This is the foundation for your apps. Your site URL identifies it. You can enable user registration and data isolation by sub-teams if needed.</li>
                            <li>Add Views to Your Site: Next, add views to your site using the dashboard tools. You can visually design pages or connect to existing URLs.</li>
                            <li>Build Your Mobile App: Apps link to sites. You configure apps with tabs that point to your site's views. The app host identifies the site and tabs to load. This data is sent back as JSON for the app to parse and display. Update tabs anytime through the admin panel.</li>
                            <li>Manage Users and Teams: Users join teams associated with sites. They get access based on role (admin or user). Admins can set up apps for their sites through the admin panel. Sub-teams provide data isolation when enabled. The main site team still has access.</li>
                        </ul>
                        <p class="mt-4 text-lg font-semibold text-gray-600 mb-4">That's the basics! Feel free to reach out if you need any help getting your first app going. We're here to make the process smooth and enjoyable. Happy building!</p>
                    </div>
                    @endif
                </div>
                @else
                @if (Auth::user()->isInstructor() && Auth::user()->getSiteCount() > 0)
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

                    {!! $user_content !!}
                </div>
                @endif
                @endif
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

                        @endif
                        @if (Auth::user()->isInstructor() )
                        <!-- Team Management -->
                        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="border-t border-gray-200 mt-2"></div>

                        <div class="border-t border-gray-200"></div>
                        <div class="block py-2 text-lg font-semibold text-gray-600">
                            {{ __('Add to Image Library') }}
                            <div class="flex">
                                <div class="px-4 py-2 border-r border-gray-200" x-data="{fileSelected: false}">
                                    <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" type="file" id="fileInput" name="image" x-ref="fileInput"
                                            @change="fileSelected = $refs.fileInput.files.length > 0">
                                        <button :disabled="!fileSelected" class="teambutton cursor-pointer mt-2 border p-2 rounded-xl px-2 py-1  text-sm  text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl" type="submit">Upload</button>
                                    </form>
                                </div>
                                <div class="px-4 py-2 ml-auto">
                                    <a class="cursor-pointer rounded-xl transition duration-500 ease-in-out transform rounded-lg shadow-xl" href="{{ route('image.library') }}" title="Image Library"><i alt="image library" class="material-icons">photo_library</i></a>
                                </div>
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

                        @if ((\App\Models\Site::isPrasso(parse_url(url()->current(), PHP_URL_HOST)) && Auth::user()->isSuperAdmin()) || (Auth::user()->isInstructor() && Auth::user()->isThisSiteTeamOwner($site->id)))
               
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
    </div>