<x-app-layout>
<x-slot name="extracss">

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
    function showRadios() {
        var checkboxes = document.getElementById("checkboxes");
        if (!expanded) {
            checkboxes.style.display = "block";
            expanded = true;
        } else {
            var selectedIcon = document.querySelector('input[name = "icon"]:checked').value;
            var divSelectedIcon = document.getElementById("selectedIcon");
            divSelectedIcon.innerHTML = '<i class="material-icons md-36">'+selectedIcon+'</i>';
            checkboxes.style.display = "none";
            expanded = false;
        }
    }
    

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
<x-slot name="title">Edit Tab</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tab') }}
        </h2>
        <x-jet-dropdown-link href="{{ route('apps.edit',['teamid' => Auth::user()->allTeams()->first()->id, 'appid' => $tabdata->app_id])   }}">
                    {{ __('Return to App') }}
                </x-jet-responsive-nav-link>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('apps.tab-info-form',[ 'tabdata'=>$tabdata, 
                         'sortorders' => $sortorders 
                        , 'moredata' => $moredata ,'icondata' => $icondata
                        ]);
            <x-jet-section-border />

        </div>
    </div>
</x-app-layout>
