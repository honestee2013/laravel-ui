<system.views::layouts.app>
    <x-slot name="sidebar">
        <system.views::layouts.navbars.auth.sidebar moduleName="access">
            <x-admin.views::layouts.navbars.auth.sidebar-links />
        </x-system.views::layouts.navbars.auth.sidebar>
    </x-slot>
    <livewire:data-tables.data-table-manager model="App\\Models\\User"
    pageTitle="User Role Assinment"
    queryFilters=[] :hiddenFields="[
            'onTable' => [],
        ]" :queryFilters="[]" />
</x-system.views::layouts.app>
