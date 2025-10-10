<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top" style="z-index: 1030;">
    <div class="container-fluid">
        {{-- Brand --}}
        {{--<a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}" wire:navigate>--}}
        <a class="navbar-brand d-flex align-items-center" href="#" wire:navigate>
            <i class="fas fa-bolt me-2"></i>
            <span class="fw-bold">QuickHR</span>
        </a>

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

                @include('core.views::components.layouts.navbars.auth.top-nav-links', [ ])


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
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile') }}" wire:navigate>
                                {{ __('qf::nav.profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-danger" type="button" wire:click="logout">
                                {{ __('qf::nav.logout') }}
                            </button>
                        </li>
                    </ul>
                </div>
                
            </div>
        </div>
    </div>
</nav>