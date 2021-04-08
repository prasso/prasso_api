<x-app-layout>

<x-slot name="title">Site</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site') }}
        </h2>
    </x-slot>

    <div class="items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
   
    <x-jet-section-border />
       
    <section class="text-gray-700 body-font">
    
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert">
                  <div class="flex">
                    <div>
                      <p class="text-sm">{{ session('message') }}</p>
                    </div>
                  </div>
                </div>
            @endif
            <div class="pull-right">
                <a class="btn btn-success" href="" title="Create a site"> <i class="fas fa-plus-circle"></i>
                    </a>
            </div>
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">No.</th>
                        <th class="px-4 py-2">Hosts</th>
                        <th class="px-4 py-2">Logo</th>
                        <th class="px-4 py-2">Color</th>
                        <th class="px-4 py-2">Database</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sites as $site)
                    <tr>
                        <td class="border px-4 py-2">{{ $site->id }}</td>
                        <td class="border px-4 py-2">{{ $site->host }}</td>
                        <td class="border px-4 py-2">{{ $site->image_logo }}</td>
                        <td class="border px-4 py-2">{{ $site->database }}</td>
                        <td class="border px-4 py-2">
                        <form action="" method="POST">
                            <a href="" title="show">
                                <i class="fas fa-eye text-success  fa-lg"></i>
                            </a>

                            <a href="">
                                <i class="fas fa-edit  fa-lg"></i>
                            </a>

                            @csrf
                            @method('DELETE')

                            <button type="submit" title="delete" style="border: none; background-color:transparent;">
                                <i class="fas fa-trash fa-lg text-danger"></i>
                            </button>
                            </form>
                       </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

    </section>  
    </div>
</x-app-layout>
