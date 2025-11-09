
<system.views::layouts.app>
    <x-slot name="sidebar">
        <system.views::layouts.navbars.auth.sidebar moduleName="access">
            <x-access.views::layouts.navbars.auth.sidebar-links />
        </x-system.views::layouts.navbars.auth.sidebar>
    </x-slot>
    <x-slot name="pageHeader">
        @include('system.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => ""])
    </x-slot>

        <livewire:access.access-controls.access-control-manager
        :selectedModule="$selectedModule?? null"
        :isUrlAccess="$isUrlAccess?? false"
        />

    <x-slot name="pageFooter">
        @include('system.views::components.layouts.navbars.auth.content-footer', [ ])
    </x-slot>
</x-system.views::layouts.app>





