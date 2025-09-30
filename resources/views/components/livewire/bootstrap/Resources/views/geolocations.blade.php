<x-core.views::layouts.app>
    <x-slot name="sidebar">
    <x-core.views::layouts.navbars.auth.sidebar moduleName="core">
    </x-core.views::layouts.navbars.auth.sidebar>
</x-slot>

    

    <livewire:data-tables.data-table-manager model="App\Modules\core\Models\Geolocation"
    pageTitle="Geolocations Management"
    queryFilters=[]
    :hiddenFields="[
  'onTable' => 
  [
  ],
  'onNewForm' => 
  [
  ],
  'onEditForm' => 
  [
  ],
  'onQuery' => 
  [
  ],
]"
    :queryFilters="[
]"
/>


    
</x-core.views::layouts.app>
