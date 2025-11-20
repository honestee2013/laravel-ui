


<system.views::layouts.app>
    <x-slot name="sidebar">
        <system.views::layouts.navbars.auth.sidebar moduleName="access">
            <x-admin.views::layouts.navbars.auth.sidebar-links />
        </x-system.views::layouts.navbars.auth.sidebar>
    </x-slot>


    <x-slot name="pageHeader">
        @include('system.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => ""])
    </x-slot>



    <livewire:dashboard.dashboard-manager moduleName="access" />



    <x-slot name="pageFooter">
        @include('system.views::components.layouts.navbars.auth.content-footer', [ ])
    </x-slot>



</x-system.views::layouts.app>




