<x-app-layout>
<x-slot name="title">Contact Us</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contact') }}
        </h2>
    </x-slot>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="tyJCtd mGzaTb baZpAe lkHyyc">
        <form method="post" action="{{ route('send-email') }}">
        @csrf
        @if ($errors->any())
            <div class="relative px-3 py-3 mb-4 border rounded text-teal-800 border-teal-900 bg-teal-300">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(Session::get('message'))
        <div class="relative px-3 py-3 mb-4 border rounded text-teal-800 border-teal-900 bg-teal-300">
                <ul>
                    <li>{{Session::get('message')}}</li>
                </ul>
            </div>
        @endif
        <div class="form-group row col-8">
            <div class="form-group col-12">
                <label for="email">Email Address(es)</label>
                <input type="text" class="block appearance-none w-full py-1 px-2 mb-1 text-base leading-normal bg-white text-grey-darker border border-grey rounded" name="email" id="email" placeholder="Example: johndoe@email.com,JohnDoe@email.com, " required>
                <small id="email" class="form-text text-muted">Enter your email address, if more than one separate with a comma</small>
            </div>
            <div class="form-group col-12">
                <label for="subject">Subject</label>
                <input type="text" class="block appearance-none w-full py-1 px-2 mb-1 text-base leading-normal bg-white text-grey-darker border border-grey rounded"" name="subject" id="subject" placeholder="Email Subject" required>
            </div>
            <div class="form-group col-12">
                <div class="form-group">
                    <label for="emailBody">Details</label>
                    <textarea class="block appearance-none w-full py-1 px-2 mb-1 text-base leading-normal bg-white text-grey-darker border border-grey rounded"" id="emailBody" name="body" rows="3"></textarea>
                </div>
            </div>
            <div class="hidden">
            <input type="text"  name="details" id="details"  >
            </div>
            <button type="submit" class="btn-blue hover:bg-gray-800 text-white w-full p-2">Send Inquiry</button>
        </div>
    </form>                          
                </div>
                
            </div>
           
   
</x-app-layout>

          
