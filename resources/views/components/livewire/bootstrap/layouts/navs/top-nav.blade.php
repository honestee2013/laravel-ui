<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top" style="z-index: 1030;">
    <div class="container-fluid">
        {{-- Brand --}}
        {{--<a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}" wire:navigate>--}}











@php
    $currentModule = request()->segment(1); // Gets 'hr', 'Admin', or 'organization'
    $moduleNames = [
        'hr' => 'Human Resource Module',
        /*'Admin' => 'Admin Control Module', 
        'organization' => 'Organization Module'*/
    ];
    $currentModuleName = $moduleNames[$currentModule] ?? 'QuickHR';
@endphp






@php
    $currentModule = request()->segment(1); // Gets 'hr', 'Admin', or 'organization'
    $moduleNames = [
        'hr' => 'QuickHR',
        /*'Admin' => 'Admin Control', 
        'organization' => 'Organization'*/
    ];
    $currentModuleName = $moduleNames[$currentModule] ?? 'QuickHR';
    $moduleIcons = [
        'hr' => 'fas fa-users',
        /*'Admin' => 'fas fa-shield-alt',
        'organization' => 'fas fa-building'*/
    ];
    $currentModuleIcon = $moduleIcons[$currentModule] ?? 'fas fa-bolt';
@endphp

<div class="dropdown" wire:ignore.self>
    <a class="navbar-brand d-flex align-items-center dropdown-toggle module-switcher" href="#" 
       data-bs-toggle="dropdown" data-bs-auto-close="true">
        <i class="{{ $currentModuleIcon }} me-2"></i>
        <span class="fw-bold">{{ $currentModuleName }}</span>
    </a>
    
    <ul class="dropdown-menu me-sm-n4 dropdown-menu-start p-3 pt-4">
        <li>
            <a class="border-radius-md dropdown-item d-flex align-items-center {{ $currentModule === 'hr' ? 'active' : '' }}" 
               href="{{ url('/hr/dashboard') }}">
                <i class="fas fa-users me-2"></i>
                HR Module
                @if($currentModule === 'hr')
                    <i class="fas fa-check ms-auto text-primary"></i>
                @endif
            </a>
        </li>
        {{--  <li>
            <a class="border-radius-md dropdown-item d-flex align-items-center {{ $currentModule === 'Admin' ? 'active' : '' }}" 
               href="{{ url('/Admin/dashboard') }}">
                <i class="fas fa-shield-alt me-2"></i>
                Admin Module
                @if($currentModule === 'Admin')
                    <i class="fas fa-check ms-auto text-primary"></i>
                @endif
            </a>
        </li>
        <li>
            <a class="border-radius-md dropdown-item d-flex align-items-center {{ $currentModule === 'organization' ? 'active' : '' }}" 
               href="{{ url('/organization/dashboard') }}">
                <i class="fas fa-building me-2"></i>
                Organization Module
                @if($currentModule === 'organization')
                    <i class="fas fa-check ms-auto text-primary"></i>
                @endif
            </a>
        </li> --}}
    </ul>
</div>

@script
<script>
document.addEventListener('livewire:init', function () {
    // Initialize dropdowns
    var moduleDropdown = new bootstrap.Dropdown(document.querySelector('.module-switcher'));
});
</script>
@endscript

















        {{-- Toggler for mobile --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topNavCollapse">
            {{-- Left: nav items (desktop) --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-none d-md-flex">
                @php
                    $visibleDesktop = array_slice($items, 0, $maxDesktop);
                    $overflowDesktop = array_slice($items, $maxDesktop);
                @endphp

                {{--@foreach($visibleDesktop as $item)
                    <li class="nav-item">
                        <a href="#"
                           class="nav-link {{ $item['key'] === $activeContext ? 'active fw-semibold' : '' }}"
                           wire:click.prevent="selectContext('{{ $item['key'] }}')">
                            <i class="fa {{ $item['icon'] }} me-1" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach--}}

                @php
                    $view = $moduleName.".views::components.layouts.navbars.auth.top-nav-links";
                @endphp
                @if(view()->exists($view))
                    @include("$view")
                @endif


                {{-- overflow dropdown --}}
                @if(count($overflowDesktop) > 0)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ __('qf::nav.more') }}
                        </a>
                        <ul class="dropdown-menu">
                            @foreach($overflowDesktop as $item)
                                <li>
                                    <a href="#" 
                                       class="dropdown-item" 
                                       wire:click.prevent="selectContext('{{ $item['key'] }}')">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            </ul>

            {{-- Center or search area (optional) --}}
            {{--<div class="mx-auto d-none d-md-block">
                {{-- Small search (example) -- }}
                <div class="input-group">
                    <input class="form-control form-control-sm" type="search" placeholder="{{ __('qf::nav.search_placeholder') }}">
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>--}}

            {{-- Right: actions / mobile nav (shows up to 3 + swipeable) --}}
            <div class="d-flex align-items-center">
                <div class="d-md-none mobile-scroll-wrapper me-2">
                    <div class="d-flex overflow-auto" style="gap:.5rem;">
                        @php
                            $visibleMobile = array_slice($items, 0, $maxMobile);
                            $overflowMobile = array_slice($items, $maxMobile);
                        @endphp

                        @foreach($visibleMobile as $item)
                            <a href="#" 
                               class="btn btn-light btn-sm {{ $item['key'] === $activeContext ? 'active' : '' }}"
                               wire:click.prevent="selectContext('{{ $item['key'] }}')">
                                <i class="fa {{ $item['icon'] }} me-1"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach

                        @if(count($overflowMobile) > 0)
                            <div class="btn-group position-static">
                                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown"></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach($overflowMobile as $item)
                                        <li>
                                            <a href="#" 
                                               class="dropdown-item" 
                                               wire:click.prevent="selectContext('{{ $item['key'] }}')">
                                                {{ $item['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Locale switcher example --}}
                <div class="me-2">
                    <select class="form-select form-select-sm" aria-label="{{ __('qf::nav.locale') }}">
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
                        <option value="fr" {{ app()->getLocale() === 'fr' ? 'selected' : '' }}>FR</option>
                        <option value="es" {{ app()->getLocale() === 'es' ? 'selected' : '' }}>ES</option>
                    </select>
                </div>

                {{-- Profile / logout --}}
                <div class="dropdown" wire:ignore>
                    <a class="btn btn-sm btn-outline-primary dropdown-toggle my-0" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i> 
                        <span class="d-none d-md-inline">{{ auth()->user()?->name ?: __('qf::nav.account') }}</span>
                    </a>
                    <ul class="dropdown-menu me-sm-n4 dropdown-menu-end p-3 pt-4">
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('profile') }}" wire:navigate>
                                 <i class="fas fa-user me-2"></i>
                                {{ __('qf::nav.profile') }} 
                            </a>
                           
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-danger border-radius-md" type="button" wire:click="logout">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                {{ __('qf::nav.logout') }}
                            </button>
                            
                        </li>
                    </ul>
                </div>
                
            </div>
        </div>
    </div>
</nav>