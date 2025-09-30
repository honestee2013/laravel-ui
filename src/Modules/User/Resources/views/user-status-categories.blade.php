<x-core.views::layouts.app>
    <x-slot name="sidebar">
    <x-core.views::layouts.navbars.auth.sidebar moduleName="user">
    </x-core.views::layouts.navbars.auth.sidebar>
</x-slot>

  <x-slot name="pageHeader">
    @include('core.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => "User Status Management"])
</x-slot>

  <x-core.views::tab-bar>
    <x-user.views::layouts.navbars.auth.user-status-management-tab-bar-links active='user-status-categories' />
</x-core.views::tab-bar>


    <livewire:data-tables.data-table-manager model="App\Modules\user\Models\UserStatusCategory"
    pageTitle="Status Categories Overview"
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


    <x-slot name="pageFooter">
    @include('core.views::components.layouts.navbars.auth.content-footer', [ ])
</x-slot>

</x-core.views::layouts.app>
