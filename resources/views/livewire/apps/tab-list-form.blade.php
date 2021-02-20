<div>
<div class="flex flex-col w-full p-8 mx-auto mt-10 border rounded-lg l md:w-1/2 md:ml-auto md:mt-0">
      
      <h2 class="mb-1 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
      {{ __('App Tabs') }}
      </h2> 

      <x-app-tabs-layout :apptabs="$apptabs" :selectedapp="$selected_app" :selectedteam="$selectedteam" />
</div>
</div>
