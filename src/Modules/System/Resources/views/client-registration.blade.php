<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">


        <!--     Fonts and icons     -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @foreach(config('qf_laravel_ui.assets.css') as $cssFile)
            <link href="{{ asset($cssFile) }}" rel="stylesheet" />
        @endforeach

                
        @livewireStyles

        @stack('styles')
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
        
        @stack('head-scripts')
    </head>

    <body class="g-sidenav-show bg-gray-100" style="padding-top: 56px; padding-bottom: 56px;">

        <livewire:qf::tenants.register-tenant />

    </body>
</html>
