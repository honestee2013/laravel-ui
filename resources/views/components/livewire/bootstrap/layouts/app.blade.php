<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!---------- ASSETS - IMAGE CROPPER  ---------->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">


    <!--- JPrint  ---->
    <link href="https://printjs-4de6.kxcdn.com/print.min.css" rel="stylesheet">


    <!--     Fonts and icons     -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @foreach (config('qf_laravel_ui.assets.css') as $cssFile)
        <link href="{{ asset($cssFile) }}" rel="stylesheet" />
    @endforeach

    @stack('styles')
</head>

<body class="g-sidenav-show bg-gray-100" style="padding-top: 56px; padding-bottom: 56px;">


    {{ $topNav ?? '' }}

    <div class="d-flex">
        <div class="sidebar-container flex-shrink-0">
            {{ $sidebar ?? '' }}
        </div>
        <div class="flex-grow-1 col-8 ">
            <main class="px-4 pt-0 mt-0">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{ $bottomBar ?? '' }}



    <!---------- ASSETS - IMAGE CROPPER  ---------->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <script src="/assets/js/plugins/flatpickr.min.js"></script>


    @foreach (config('qf_laravel_ui.assets.js') as $jsFile)
        <script src="{{ asset($jsFile) }}"></script>
    @endforeach

    @stack('script')

</body>

</html>
