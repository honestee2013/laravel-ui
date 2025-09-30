<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts and CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fontawesome5-fullcss@1.1.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="{{ asset('vendor/qf/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/qf/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('vendor/qf/css/soft-ui-dashboard.css')}}" rel="stylesheet" />

    @livewireStyles
    @stack('styles')
</head>

<body class="g-sidenav-show bg-gray-100">
    {{ $slot }} {{-- Livewire AppLayout will be mounted here --}}

    @livewireScripts
    <script src="{{ asset('vendor/qf/js/core/bootstrap.bundle.min.js')}} "></script>
    <script src="{{ asset('vendor/qf/js/soft-ui-dashboard.min.js?v=1.0.3')}} "></script>
    <script src="{{ asset('vendor/qf/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('vendor/qf/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('vendor/qf/js/plugins/fullcalendar.min.js') }}"></script>
    <script src="{{ asset('vendor/qf/js/plugins/chartjs.min.js') }}"></script>
    @stack('footer-scripts')
</body>
</html>
