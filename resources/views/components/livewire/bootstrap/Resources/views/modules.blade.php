

<div style="max-width: 70em; margin: auto;">
<system.views::layouts.app>
    {{--<x-slot name="sidebar">
        <system.views::layouts.navbars.auth.sidebar moduleName="Admin">
            <x-Admin.views::layouts.navbars.auth.sidebar-links />
        </x-system.views::layouts.navbars.auth.sidebar>
    </x-slot>--}}
    <x-slot name="pageHeader">
        @include('system.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => "System Modules"])
    </x-slot>

    <div class="row g-5 p-5 ">
        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-users',
            'title' => 'Human Resource',
            'url' => '/hr/dashboard',
            ]
        )



        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-key',
            'title' => 'Admin Control',
            'url' => '/Admin/dashboard',
            ]
        )


        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-user-cog',
            'title' => 'User Management',
            'url' => '/user/dashboard',
            ]
        )




        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-city',
            'title' => 'Enterprise Info',
            'url' => '/enterprise/dashboard',
            ]
        )



        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-history',
            'title' => 'Activity Logs',
            'url' => '/log/dashboard',
            ]
        )


{{--

        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-warehouse',
            'title' => 'Warehouse Mangement',
            'url' => '/warehouse/dashboard',
            ]
        )


        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-cubes',
            'title' => 'Inventory Mangement',
            'url' => '/inventory/dashboard',
            ]
        )


        @include('system.views::module-menu-icon', [
            'icon' => 'fas fa-boxes',
            'title' => 'Production Mangement',
            'url' => '/production/dashboard',
            ]
        )

--}}


    </div>

    <x-slot name="pageFooter">
        @include('system.views::components.layouts.navbars.auth.content-footer', [ ])
    </x-slot>
</x-system.views::layouts.app>

</div>





