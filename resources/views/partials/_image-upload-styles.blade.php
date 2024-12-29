<!-- Image Upload Styles -->
<style>
    .spinner {
        display: inline-block;
        width: 1em;
        height: 1em;
        margin-right: 0.5em;
        vertical-align: middle;
        border: 0.2em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-rotation 0.75s linear infinite;
    }

    @keyframes spinner-rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .teambutton {
        background-color: #5C5C45;
    }
    
    .teambutton:hover {
        background-color: #424231;
    }
</style>
