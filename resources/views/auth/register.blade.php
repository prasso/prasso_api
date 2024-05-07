<x-guest-layout>
    <x-slot name="title">Register</x-slot>
@if(isset($site))
@if ($site->supports_registration) 
    <x-authentication-card>
        <x-slot name="logo">
            <img style='max-width:150px' src="{{ $site->logo_image }}" />
        </x-slot>

        <x-validation-errors class="mb-4" />
        @if(Session::get('message'))
        <div class="relative px-3 py-3 mb-4 border rounded text-teal-800 border-teal-900 bg-teal-300">
                <ul>
                    <li>{{Session::get('message')}}</li>
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label value="{{ __('Name') }}" />
                <x-input class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                @if (app('request')->input('email'))
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ app('request')->input('email') }}" required />
                @else
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                @endif
                <input type="hidden" name="billing_address"  />
             </div>
            @if (app('request')->input('invite'))
            <x-input id="invite" class="hidden" type="hidden" name="invite" value="{{ app('request')->input('invite') }}" required />
            @endif


            <div class="mt-4">
                <x-label value="{{ __('Password') }}" />
                <x-input class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label value="{{ __('Confirm Password') }}" />
                <x-input class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 mr-4" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
@endif
@endif
</x-guest-layout>