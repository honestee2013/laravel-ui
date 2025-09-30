<x-core.views::layouts.app>
    <x-slot name="sidebar">
    <x-core.views::layouts.navbars.auth.sidebar moduleName="user">
    </x-core.views::layouts.navbars.auth.sidebar>
</x-slot>

  <x-slot name="pageHeader">
    @include('core.views::components.layouts.navbars.auth.content-header', [ "pageTitile" => "Users Management"])
</x-slot>

  <x-core.views::tab-bar>
    <x-user.views::layouts.navbars.auth.users-management-tab-bar-links active='users' />
</x-core.views::tab-bar>


    <livewire:data-tables.data-table-manager model="App\Modules\user\Models\User"
    pageTitle="User Overview"
    queryFilters=[]
    :hiddenFields="[
  'onTable' => 
  [
    0 => 'password',
    1 => 'remember_token',
    2 => 'email_verified_at',
    3 => 'password_confirmation',
  ],
  'onNewForm' => 
  [
    0 => 'email_verified_at',
    1 => 'remember_token',
  ],
  'onEditForm' => 
  [
    0 => 'email_verified_at',
    1 => 'remember_token',
  ],
  'onQuery' => 
  [
    0 => 'password',
    1 => 'remember_token',
    2 => 'password_confirmation',
  ],
]"
    :queryFilters="[
]"
/>


    <x-slot name="pageFooter">
    @include('core.views::components.layouts.navbars.auth.content-footer', [ ])
</x-slot>

</x-core.views::layouts.app>
