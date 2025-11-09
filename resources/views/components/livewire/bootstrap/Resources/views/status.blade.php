
<system.views::layouts.app>
    <x-slot name="sidebar">
        <system.views::layouts.navbars.auth.sidebar moduleName="core">
            <system.views::layouts.navbars.auth.sidebar-links />
        </x-system.views::layouts.navbars.auth.sidebar>
    </x-slot>

    {{--<livewire:data-tables.data-table-manager model="App\\Modules\\system\\Models\\Status" /> --}}
</x-system.views::layouts.app>

