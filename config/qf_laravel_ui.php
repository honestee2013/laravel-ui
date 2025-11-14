<?php

return [
    // Set the entire Application UI framework 
    // Note that the resources/views/components/[blade/livewire]/[bootstrap/tailwind]/* components shuld be used accordingly
    'ui_framework' => env('UI_FRAMEWORK', 'bootstrap'), // or 'tailwind'

    // Whether to load the package routes (dashboard, settings, etc.)
    // If you set to false, you can define your own routes
    // and point to the Livewire components as needed
    'load_routes' => true,

    // Inside the pulic directory    
    "assets" => [
        "css" => [
            "assets/css/nucleo-icons.css",
            "assets/css/nucleo-svg.css",
            "assets/css/soft-ui-dashboard.min.css",
        ],
        "js" => [
            "assets/js/core/bootstrap.bundle.min.js", // Plus popperjs
            "assets/js/soft-ui-dashboard.min.js",
            "assets/js/plugins/sweetalert.min.js",
            "assets/js/plugins/chartjs.min.js",
        ] 
    ],

];