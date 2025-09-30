<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Fonts -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <!-- Livewire Styles & Scripts -->
        @livewireStyles
        @stack('styles')
        
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>

        @stack('head-scripts')
    </head>

    <body>

@auth        

        {{-- Top Bar Slot --}}
        {{ $topNav ?? '' }}


        <div class="d-flex">
            {{-- Sidebar --}}
            {{ $sidebar ?? '' }}

            {{-- Main Content --}}
            <div class="flex-grow-1">
                <main class="p-4">
                    {{ $slot }}
                </main>
            </div>

        </div>

        {{-- Bottom bar --}}
        {{ $bottomBar ?? '' }}

    @endauth   


        @livewireScripts

        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
            integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
        </script>
        @stack('footer-scripts')

    </body>
</html>
