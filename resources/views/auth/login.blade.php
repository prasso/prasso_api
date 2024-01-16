<x-guest-layout>

<x-slot name="title">Login</x-slot>
    <x-authentication-card>
        <x-slot name="logo">
        @isset($site)
        <img class="max-w-md" src="{{ $site->logo_image }}" />
        @endisset
        </x-slot>

        <x-validation-errors class="mb-4" />

        
            <div class="mb-4 font-medium text-sm text-green-600">
                @if (session('status'))
                {{ session('status') }}<br>
                @endif
                <a class='underline' href="/register">Register</a> for this site.
            </div>
        
        @if(Session::get('error_msg'))
            <div class="alert alert-danger alert-dismissable server-error">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Error !</h4>
                {{Session::get('error_msg')}}
            </div>
         @endif                   
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label value="{{ __('Email') }}" />
                <x-input class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="mt-4">
                <x-label value="{{ __('Password') }}" />
                <x-input class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
            @if (Route::has('password.request'))
                    <a class="float-right underline text-sm text-gray-600 hover:text-gray-900 " href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
              
            </div>

            <div class="flex items-center justify-end mt-4">
                <div class="m-auto"><x-button>
                    {{ __('Login') }}
                </x-button></div>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
