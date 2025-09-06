<?php

return [
    // Set the entire Application UI framework 
    // Note that the resources/views/components/[blade/livewire]/[bootstrap/tailwind]/* components shuld be used accordingly
    'ui_framework' => env('UI_FRAMEWORK', 'bootstrap'), // or 'tailwind'

    // Whether to load the package routes (dashboard, settings, etc.)
    // If you set to false, you can define your own routes
    // and point to the Livewire components as needed
    'load_routes' => true,

];