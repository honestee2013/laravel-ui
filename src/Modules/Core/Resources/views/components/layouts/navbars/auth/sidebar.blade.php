


<div id="sidebar" class="sidebar col-1 border-0 border-radius-xl my-3 fixed-start ms-3 ">
  <livewire:menus.menu-initializer moduleName="{{$moduleName}}"  />

  <ul class="navbar-nav">
    {{--<x-core.views::layouts.navbars.auth.sidebar-header moduleName="{{$moduleName}}" />--}}
        {{ $slot }}
    {{--<x-core.views::layouts.navbars.auth.sidebar-footer moduleName="{{$moduleName}}" />--}}
  </ul>
</div>








