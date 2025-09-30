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

<body class="g-sidenav-show  bg-gray-100">

        

{{-- Top Bar Slot --}}
{{ $topNav ?? '' }}

<div class="d-flex">
    {{-- Sidebar + Toggle --}}
    <div class="sidebar-container flex-shrink-0">
        {{ $sidebar ?? '' }}
    </div>

    {{-- Main Content --}}
    <div class="flex-grow-1 col-8">
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>
</div>



{{-- Bottom bar --}}
{{ $bottomBar ?? '' }}




   


        @livewireScripts

        <!-- Bootstrap JS Bundle with Popper -->

        @stack('footer-scripts')



        @foreach(config('qf_laravel_ui.assets.js') as $jsFile)
            <script src="{{ asset($jsFile) }}"></script>
        @endforeach




<script>
    // Handle browser navigation (back/forward buttons)
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.page) {
            Livewire.dispatch('pageChanged', [event.state.page, event.state.params || {}]);
        }
    });

    // Listen for page changes from Livewire and update URL
    Livewire.on('page-changed', (data) => {
        // Update browser history without reloading
        history.pushState(
            { page: data.page, params: data.params },
            '',
            '/' + data.page
        );
        
        // Update document title if needed
        ///document.title = data.page.charAt(0).toUpperCase() + data.page.slice(1) + ' - ' + 'Your App Name';
    });
</script>

    </body>
</html>
