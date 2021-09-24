<x-app-layout>
    <x-slot name="extracss">

        <meta name="_token" content="{{$access_token}}">
        <style>
            .multiselect {
                width: 80%;
            }

            .selectBox {
                position: relative;
            }

            .selectBox select {
                width: 100%;
                font-weight: bold;
            }

            .overSelect {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                bottom: 0;
            }

            [type="checkbox"],
            [type="radio"] {
                margin: 3px;
            }

            #checkboxes {
                display: none;
                border: 1px #dadada solid;
                margin: 3px;
            }

            #checkboxes label {
                display: block;
            }

            #checkboxes label:hover {
                background-color: #1e90ff;
            }
        </style>

    </x-slot>
    <x-slot name="extrajs">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/themes/airbnb.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
        <script>
            var expanded = false;
            var useremail='<?php echo $user_email; ?>';
           
            function showCheckboxes(expanded) {
                var checkboxes = document.getElementById("checkboxes");
                if (!expanded || useremail != '') {
                    checkboxes.style.display = "block";
                    expanded = true;
                } else {
                    checkboxes.style.display = "none";
                    expanded = false;
                }
            }
            showCheckboxes(true);
            const today = new Date();
            let date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate() + ' ' + today.getHours() + ':' + today.getMinutes();
            flatpickr("#schedule_date_time", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                inline: true,
                defaultDate: date,
                minuteIncrement: 1
            });
        </script>
            
          
    </x-slot>
    <x-slot name="title">Team Messaging</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Messaging') }}
        </h2>
        <x-jet-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
            {{ __('Team Settings') }}
        </x-jet-responsive-nav-link>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class='md:grid md:grid-cols-3 md:gap-6'>
    <x-jet-section-title>
        <x-slot name="title"> {{ __('Team Communications') }}</x-slot>
        <x-slot name="description">{{ __('Create and Schedule Team Communications') }}</x-slot>
    </x-jet-section-title>
    @if (session()->has('message'))
    <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert">
        <div class="flex">
            <div>
                <p class="text-sm">{{ session('message') }}</p>
            </div>
        </div>
    </div>
    @endif
    <div class="mt-5 md:mt-0 md:col-span-2">
        <form id="teamMessages" method="post" action="{{ route('team.postmessages', Auth::user()->currentTeam->id)}}">
        <input type="hidden" name="csrf-token" value="{{ csrf_token() }}" />
        <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
         
        <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                       {{$user_email}}
                       <div class="text-lg">Push Notifications</div>
                        <div class="col-span-6 sm:col-span-4">
                            
                            <x-jet-label for="subject" value="{{ __('Message Subject') }}" />

                            <x-jet-input id="subject" name="subject" type="text" class="mt-1 block w-full"  value="{{old('subject') ?? $formdata['notifications']->subject}}"   />

                            <x-jet-input-error for="subject" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="body" value="{{ __('Message Body') }}" />
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="body" name="body" value="{{old('body') ?? $formdata['notifications']->body}}" placeholder="Enter Body"></textarea>
                            @error('body') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="text-lg">Emails</div>
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="emailselection" value="{{ __('Email Selection') }}" />

                            <x-jet-input id="emailToSend" name="emailToSend" type="text" class="mt-1 block w-full"  value="{{old('emailToSend') ?? $formdata['notifications']->emailToSend}}"   />

                            <x-jet-input-error for="subject" class="mt-2" />
                        </div>
                    
                    <div class="col-span-6">
                        <x-jet-label value="{{ __('Select recipient') }}" />
                        <div class="flex items-center mt-2">

                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes()">
                                    <select>
                                        <option>Select one or more recipients</option>
                                    </select>
                                    <div class="overSelect"></div>
                                </div>
                                <div id="checkboxes">
                                    @foreach($formdata['recipients'] as $id=>$teammember)
                                    <label for="member-{{ $teammember->email }}">
                                        <input type="checkbox" value="member-{{$teammember->id}}" name="member-{{ $teammember->id }}" @if($user_email==$teammember->email) checked="checked" @endif id="member-{{ $teammember->id }}" />{{ $teammember->name }}</label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="schedule_date_time" value="{{ __('Date and Time to Send') }}" />

                        <x-jet-input id="schedule_date_time" name="schedule_date_time"  value="{{old('schedule_date_time')  ?? $formdata['notifications']->schedule_date_time}}"  type="text" class="mt-1 block w-full"  />

                        <x-jet-input-error for="schedule_date_time" class="mt-2" />
                    </div>

                    </div>
                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <input type="submit" class="cursor-pointer ml-6 border p-2 rounded-xl" value="{{ __('Schedule Message(s)') }}" />
                </div>
               
            </div>
        </form>
    </div>

        </div>
    </div>
</x-app-layout>
