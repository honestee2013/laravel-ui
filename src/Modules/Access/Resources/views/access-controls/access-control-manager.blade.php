    <x-dashboard.views::layouts.dashboards.default-dashboard>
        @hasanyrole('admin|super_admin')
        <x-slot name="mainTitle"> <strong class="text-info text-gradient">{{ $selectedScope?->name}}</strong> Access Control</x-slot>
            <x-slot name="subtitle"> {{ $selectedModuleName? ucfirst($selectedModuleName. " Module"): ''}}</x-slot>
            <x-slot name="controls">
                @include("access.views::access-controls.module-selector")
            </x-slot>

            @if($showResourceControlButtonGroup)
                <div class="row g-5">
                    @foreach ($this->resourceNames as $key => $resourceName)
                        @php
                            $preparedResourceName = str_replace('_', ' ',Str::snake($resourceName));
                            $title = ucwords($preparedResourceName) . " Management";
                            $subtitle = "<div class='text-xs mt-2'> What <strong class='text-dark'>".$selectedScope?->name."</strong> can do on <strong class='text-dark'>". ucfirst($preparedResourceName) . " records?</strong></div>";
                        @endphp


                        <div class="col-12 col-sm-6">
                            <livewire:widgets.buttons.toggle-button-group
                                :title="$title"
                                :subtitle="$subtitle"
                                :componentId="$resourceName.'-'.$key"
                                :buttons="$resourceControlButtonGroup[$resourceName]?? []"
                                :groupId="$resourceName"
                                stateSyncMethod="method"
                                :data="[
                                    'selectedScope' => $this->selectedScope,
                                    'selectedScopeId' => $this->selectedScopeId,
                                    'resourceName' => $resourceName,
                                    'controlsCSSClasses' => $controlsCSSClasses,
                                ]"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
         @endhasanyrole 
    </x-dashboard.views::layouts.dashboards.default-dashboard>
