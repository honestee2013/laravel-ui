<section class="m-0 my-md-4">

    <livewire:hr.payroll-run-preview />
 
    {{-- Main Modal for Add/Edit --}}
    <x-qf::livewire.bootstrap.data-tables.modals.add-edit-modal
        :modalId="$modalId"
        :isEditMode="$isEditMode"
        :pageTitle="$pageTitle"
        :queryFilters="$queryFilters"
        :configFileName="$configFileName"
        :config="$config"
        :readOnlyFields="$readOnlyFields"
        :fieldGroups="$fieldGroups"
        :fieldDefinitions="$fieldDefinitions"
        :model="$model"
        :moduleName="$moduleName"
        :modelName="$modelName"
        :recordName="$recordName"
        :multiSelectFormFields="$multiSelectFormFields"
        :singleSelectFormFields="$singleSelectFormFields"
        :hiddenFields="$hiddenFields"
        :columns="$columns"
    />

    {{-- Content Begins --}}
    <div class="card p-4">
        {{-- Header --}}
        <x-qf::livewire.bootstrap.data-tables.partials.table-header
            :config="$config"
            :pageTitle="$pageTitle"
            :modelName="$modelName"
            :config="$config"
            :controls="$controls"
            :viewType="$viewType" 
            :selectedItemId="$selectedItemId"
            :model="$model"
            :moduleName="$moduleName"

        />

        

        {{-- Data Table Controls --}}
        <div class="container ms-0 mt-4 mb-0">
            <livewire:qf::data-tables.data-table-control 
                :controls="$controls"
                :columns="$columns"
                :hiddenFields="$hiddenFields"
                :visibleColumns="$visibleColumns"
                :model="$model"
                :fieldDefinitions="$fieldDefinitions"
                :multiSelectFormFields="$multiSelectFormFields"
                :sortField="$sortField"
                :sortDirection="$sortDirection"
                :perPage="$perPage"
                :moduleName="$moduleName"
                :modelName="$modelName"
                :recordName="$recordName"
                :config="$config"

                :viewType="$viewType" 
            />
        </div>

        {{-- Data Table --}}
        <livewire:qf::data-tables.data-table
            :fieldDefinitions="$fieldDefinitions"
            :hiddenFields="$hiddenFields"
            :multiSelectFormFields="$multiSelectFormFields"
            :queryFilters="$queryFilters"
            :columns="$columns"
            :model="$model"
            :simpleActions="$simpleActions"
            :controls="$controls"
            :visibleColumns="$visibleColumns"
            :sortField="$sortField"
            :sortDirection="$sortDirection"
            :perPage="$perPage"
            :moduleName="$moduleName"
            :modelName="$modelName"
            :recordName="$recordName"
            :moreActions="$moreActions"
            :config="$config"
            :viewType="$viewType"

            :selectedItemId="$selectedItemId"

        />

        {{-- Show Detail Modal --}}
        <x-qf::livewire.bootstrap.data-tables.modals.show-detail-modal
            :fieldDefinitions="$fieldDefinitions"
            :selectedItem="$selectedItem"
            :isEditMode="$isEditMode"
            :model="$model"
            :modelName="$modelName"
            :columns="$columns"
            :hiddenFields="$hiddenFields"
            :multiSelectFormFields="$multiSelectFormFields"
        />


           
            {{--@php
                $path = 'qf::components.livewire.bootstrap.data-tables.modals.show-detail-modal';
            @endphp
            @if(view()->exists($path))
                @include("$path", ['selectedItem' => $selectedItem])
            @endif--}}



            
    </div>
</section>

{{--
@php
    $assets = 'qf::components.livewire.bootstrap.resources.assets.data-tables.assets';
    $scripts = 'qf::components.livewire.bootstrap.resources.assets.data-tables.scripts';
@endphp
@if(view()->exists($assets))
    @include("$assets")
@endif
@if(view()->exists($scripts))
    @include("$scripts")
@endif

--}}