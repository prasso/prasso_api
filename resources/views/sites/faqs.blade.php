<x-app-layout>

<x-slot name="title">FAQS</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Frequently Asked Questions') }}
        </h2>
       
    </x-slot>

    <div  class="container mx-auto font-sans text-center prose prose-sm sm:prose lg:prose-lg xl:prose-xl">

    <div class="mx-auto my-4 px-4 py-4 text-xl font-medium">
        <form action="/question" method="POST" name='faqquestion' id='faqquestion' class="w-84 mx-auto">
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
        <div class="mb-4">
            <label class="block mb-2">What can we help you with?</label>
            <input type="text" name="question" class="border w-full p-1">
        </div>
        <div class="mb-4">
            <label class="block mb-2">How may we reply? (email perhaps?)</label>
            <input type="text" name="email" class="border w-full p-1">
        </div>
        
        <button class="btn-blue hover:bg-gray-800 text-white w-full p-2">Send my question to the support team</button>
    </form>

    <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Question</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($faqs as $faq)
                    <tr>
                        <td class="border px-4 py-2"><strong>{{ $faq->title }}</strong><br/>
                        {{ $faq->details }}</td>                       
                    </tr>
                    @endforeach
                </tbody>
            </table>
    </div> 
</div>




</x-app-layout>
  