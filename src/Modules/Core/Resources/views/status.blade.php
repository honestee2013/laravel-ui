
<x-core.views::layouts.app>
    <x-slot name="sidebar">
        <x-core.views::layouts.navbars.auth.sidebar moduleName="core">
            <x-core.views::layouts.navbars.auth.sidebar-links />
        </x-core.views::layouts.navbars.auth.sidebar>
    </x-slot>

    <livewire:data-tables.data-table-manager model="App\\Modules\\Core\\Models\\Status" />
</x-core.views::layouts.app>

