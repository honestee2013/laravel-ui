

<div>

<x-slot name="topNav">
    <livewire:qf::layouts.navs.top-nav  />
</x-slot>

<x-slot name="sidebar">
    <livewire:qf::layouts.navs.sidebar context="people" />
</x-slot>

<x-slot name="bottomBar">
    <livewire:qf::layouts.navs.bottom-bar />
</x-slot>


        @include($viewPath)


</div>
