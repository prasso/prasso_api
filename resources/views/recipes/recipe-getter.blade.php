<x-app-layout>

<x-slot name="title">Add New Recipes</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recipe Scraper') }}
        </h2>
       
    </x-slot>

   
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

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
            <recipe-list>
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">No.</th>
                        <th class="px-4 py-2">Recipe Site</th>
                        <th class="px-4 py-2 w-25">Actions Available</th>
                    </tr>
                </thead>
                <tbody>
                  @if(isset($recipe_sites))
                    @foreach($recipe_sites as $recipe_site)
                    <tr @if ($recipe_site->recipes_have_been_added=="1") class="bg-gray-100" @endif>
                        <td class="border px-4 py-2">{{ $recipe_site->id }}</td>
                        <td class="border px-4 py-2 overflow-hidden">{{ $recipe_site->site_url }}</td>
                        <td class="border px-4 py-2">
                        <div style="float-left" x-data="{recipe_id:'{{$recipe_site->id}}'}">
                          <button id="site{{$recipe_site->id}}" onclick="runscraper({{$recipe_site->id}}, {{$recipe_site->recipes_have_been_added}})" class=" py-2 px-3 "> <i class="material-icons md-36">mode_edit</i></button>
                          <button id="parse{{$recipe_site->id}}" onclick="runparser({{$recipe_site->id}}, {{$recipe_site->recipes_have_been_added}})" class=" py-2 px-3 "> <i class="material-icons md-36">mode_comment</i></button>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
            </table>
            </recipe-list>
        </div>
    </div>
</div>

        </div>
    </div>




<script src="{{ asset('/js/recipe-scraper.js') }}" defer></script>
</x-app-layout>




