<x-app-layout :site="$site ?? null">

<x-slot name="title">Profile</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="w-768px bg-color-gray-200 m-auto" style="width:768px;" >
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
        <button type="button" style="float:none !important" class="btn-blue rounded mb-0 py-2 px-1 hover:bg-gray-800 text-white" data-dismiss="alert">×</button>
        <strong>{{ $message }}</strong>
        </div>
    @endif
    @if (count($errors) > 0)
    <div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.
    <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
    </ul>
    @endif
    <!-- Current Profile Photo -->
    <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $user->getProfilePhoto() }}" alt="{{ $user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>
        <form action="{{ url('/profile/profile_update_image') }}" method="POST" enctype="multipart/form-data" class="form-horizontal">
            {{ csrf_field() }}
            <div class="form-group">
            <input type="file" name="image" id="image">
            </div>
            <div class="form-group">
            <button type="submit" style="float:none !important" class="btn-blue rounded mb-0 mt-3 py-2 px-3 hover:bg-gray-800 text-white">Upload</button>
            </div>
        </form>
        <form action="/account/initial-profile" method="POST" name='initialprofile' id='initialprofile'>
            {{ csrf_field() }}
              <div class="flex flex-wrap mb-6">
                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mt-6 mb-2"
                        for="grid-first-name">
                        First Name
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4  leading-tight focus:outline-none focus:bg-white"
                        id="grid-first-name" type="text" placeholder="Jane">
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-last-name">
                        Last Name
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-last-name" type="text" placeholder="Doe">
                </div>
            </div>
            <div class="flex flex-wrap mb-6">
                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-first-name">
                        Current Weight (lbs)
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white"
                        id="grid-current-weight" type="text" placeholder="220">
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-last-name">
                        Goal Weight (lbs)
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-goal-weight" type="text" placeholder="150">
                </div>
            </div>
            <div class="flex flex-wrap mb-6">
                <div class="w-full md:w-1/2 px-3  mb-6">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-last-name">
                        Male / Female
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-male-female" type="text" placeholder="">
                </div>
                <div class="w-full md:w-1/2 px-3  md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-first-name">
                        Height (ft/in)
                    </label>

                    <input
                        class="py-3 px-4 appearance-none bg-white text-gray-700 border border-gray-200 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-height-ft" type="text" placeholder="5">
                    <input
                        class="py-3 px-4 appearance-none bg-white text-gray-700 border border-gray-200 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-height-in" type="text" placeholder="8">

                </div>
            </div>
            <div class="flex flex-wrap mb-6">
                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-first-name">
                        Current Activity Level
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white"
                        id="grid-current-activity-level" type="text" placeholder="light">
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-last-name">
                        Goal Activity Level
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-goal-activity-level" type="text" placeholder="active">
                </div>
            </div>
            <div class="flex flex-wrap mb-6">
                <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-calories">
                        Calories (kcal)
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white"
                        id="grid-calories" type="text" placeholder="1625">
                </div>
                <div class="w-full md:w-1/4 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-fat">
                        Fat (g)
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 mb-6 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-fat" type="text" placeholder="46.94">
                </div>
                <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-carbs">
                        Carbs (g)
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white"
                        id="grid-carbs" type="text" placeholder="195.00">
                </div>
                <div class="w-full md:w-1/4 px-3">
                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
                        for="grid-protein-allowe">
                        Protein
                    </label>
                    <input
                        class="appearance-none block w-full bg-white text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        id="grid-protein-allowed" type="text" placeholder="105.62">
                </div>
            </div>
            <button style="float:none !important" class="btn-blue rounded mb-0 py-2 px-1 hover:bg-gray-800 text-white w-full">Save
                Changes</button>
           
        </form>
        <div style="clear:both; " class="mb-10"></div>
    </div>
   
</x-app-layout>