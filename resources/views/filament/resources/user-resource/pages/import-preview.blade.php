<x-filament-panels::page>
    {{ $this->form }}

    @if($hasUploadedFile)
    <form wire:submit="import" class="mt-6">
        <div class="flex items-center justify-end gap-x-3 mt-6">
            <x-filament::button 
                type="button" 
                color="gray" 
                wire:click="cancel"
            >
                Cancel
            </x-filament::button>

            <x-filament::button 
                type="submit" 
                color="success"
            >
                Import {{ $total_rows }} Users
            </x-filament::button>
        </div>
    </form>
    @endif
</x-filament-panels::page>
