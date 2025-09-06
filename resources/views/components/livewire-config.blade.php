@once
@push('scripts')
<script>
    // Tell Livewire not to load Alpine.js
    window.livewireScriptConfig = {
        skipAlpine: true
    };
</script>
@endpush
@endonce
