<div class="d-none d-md-flex"> {{-- Hides on <768px (mobile) --}}
    {{-- Sidebar container --}}


    <div
        class="sidebar-container bg-light border-end vh-100 d-flex flex-column align-items-stretch
                @if ($state === 'full') sidebar-full
                @elseif($state === 'icon') sidebar-icon
                @else sidebar-hidden @endif">
        {{-- Nav items --}}
        <ul class="nav flex-column mt-3">
            {{--@foreach ($contextItems as $item)
                <li class="nav-item text-nowrap">
                    <a href="{{ url($item['url']) }}" class="nav-link d-flex align-items-center" data-bs-toggle="tooltip"
                        data-bs-placement="right" title="{{ $item['title'] }}" wire:navigate>
                        <i class="fa {{ $item['icon'] }} me-2"></i>
                        @if ($state === 'full')
                            <span>{{ $item['title'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach--}}

            @php
                $path = "{$moduleName}.views::components.layouts.navbars.auth.{$context}.sidebar-links";
            @endphp
            @if(view()->exists($path))
                @include("$path", ["state"=> $state])
            @endif

        </ul>
    </div>

    {{-- Toggle handle (always visible on desktop) --}}
    <div class="d-flex align-items-center justify-content-center toggle-handle bg-light border-end"
        style="cursor:pointer; width:20px;" wire:click="toggleState">
        <i
            class="fa fa-chevron-left toggle-icon text-primary
            @if ($state === 'full' || $state === 'icon') rotated-left
            @else rotated-right @endif">
        </i>
    </div>



<style>
        .sidebar-container {
            transition: all 0.3s ease-in-out; /* 👈 smooth animation */
            overflow: hidden; /* prevent content from spilling */
        }

        .sidebar-full {
            width: 220px;
            min-width: 220px;
        }

        .sidebar-icon {
            width: 60px;
            min-width: 60px;
        }

        .sidebar-hidden {
            width: 0;
            min-width: 0;
        }

        .sidebar-hidden .nav-link {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .sidebar-full .nav-link,
        .sidebar-icon .nav-link {
            opacity: 1;
            transition: opacity 0.2s ease-in-out 0.1s;
        }



    /* Toggle icon rotation */
    .toggle-icon {
        transition: transform 0.3s ease-in-out;
    }

    .rotated-left {
        transform: rotate(0deg);   /* default left */
    }

    .rotated-right {
        transform: rotate(180deg); /* flipped smoothly */
    }


    </style>
</div>


