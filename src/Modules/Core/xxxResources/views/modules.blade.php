

<div style="max-width: 70em; margin: auto;">
<x-core.views::layouts.app>
    {{--<x-slot name="sidebar">
        <x-core.views::layouts.navbars.auth.sidebar moduleName="access">
            <x-access.views::layouts.navbars.auth.sidebar-links />
        </x-core.views::layouts.navbars.auth.sidebar>
    </x-slot>--}}
    <x-slot name="pageHeader">
        @include('core.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => "System Modules"])
    </x-slot>

    <div class="row g-5 p-5 ">
        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-users',
            'title' => 'Human Resource',
            'url' => '/hr/dashboard',
            ]
        )



        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-key',
            'title' => 'Access Control',
            'url' => '/access/dashboard',
            ]
        )


        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-user-cog',
            'title' => 'User Management',
            'url' => '/user/dashboard',
            ]
        )




        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-city',
            'title' => 'Enterprise Info',
            'url' => '/enterprise/dashboard',
            ]
        )



        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-history',
            'title' => 'Activity Logs',
            'url' => '/log/dashboard',
            ]
        )


{{--

        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-warehouse',
            'title' => 'Warehouse Mangement',
            'url' => '/warehouse/dashboard',
            ]
        )


        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-cubes',
            'title' => 'Inventory Mangement',
            'url' => '/inventory/dashboard',
            ]
        )


        @include('core.views::module-menu-icon', [
            'icon' => 'fas fa-boxes',
            'title' => 'Production Mangement',
            'url' => '/production/dashboard',
            ]
        )

--}}


    </div>

    <x-slot name="pageFooter">
        @include('core.views::components.layouts.navbars.auth.content-footer', [ ])
    </x-slot>
</x-core.views::layouts.app>

</div>





