<x-guest-layout>

<x-slot name="title">Forgot Password</x-slot>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <img src="{{ asset('images/mercy_full_logo.jpg') }}" />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-jet-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-jet-label value="{{ __('Email') }}" />
                <x-jet-input class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-jet-button>
                    {{ __('Email Password Reset Link') }}
                </x-jet-button>
            </div>
            <a class="float-right underline text-sm text-gray-600 hover:text-gray-900 mt-5  " href="/login">
                        Return to Login
                    </a>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
