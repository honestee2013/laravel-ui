<section class="m-0 my-md-4">

    {{-- ----------------- MAIN MODAL FOR ADD-EDIT ----------------- --}}
    @include('core.views::data-tables.modals.modal-header', [
        'modalId' => $modalId,
        'isEditMode' => $isEditMode,
    ])
        <div class="card-body">
            {{-- REACTIVE FORM COMPONENT --}}
            <livewire:data-tables.data-table-form
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
                :isEditMode="$isEditMode"
                modalId="addEditModal"
                key="addEditModal" />
        </div>
    @include('core.views::data-tables.modals.modal-footer', [
        'modalId' => $modalId,
        'isEditMode' => $isEditMode,
    ])


    {{-- ------------ CONTENT BEGINS ------------ --}}
    <div class="card  p-4">

        {{-- ------------DATA TABLE MANAGER HEADER TEXT ------------ --}}
        <div class="card-header pb-0">
            <div class="d-flex flex-row justify-content-between">
                <div>
                    @php
                        if (!$pageTitle) // If 'pageTitle' is not available in the DataTableManager check the config file
                            $pageTitle = $config['pageTitle']?? '';

                        if (!$pageTitle) { // If 'pageTitle' is not available in the config file, generate it from the modelName
                            $pageTitle = Str::snake($modelName); // Convert to snake case
                            $pageTitle = ucwords(str_replace('_', ' ', $pageTitle)); // Convert to capitalised words
                            $pageTitle = Str::plural(ucfirst($pageTitle))." Record";
                        }
                    @endphp

                    <h5 class="mb-4">{{ $pageTitle }} </h5>

                </div>
                @if (is_array($controls) && isset($controls['addButton']) && $controls['addButton'])
                    <button wire:click="$dispatch('openAddModalEvent')"
                        class="btn bg-gradient-primary btn-icon-only rounded-circle" type="button">
                        <i class="fa-solid fa-plus   text-white"></i>
                    </button>
                @endif
            </div>
        </div>



        {{-- @include('core.views::data-tables.partials.feedback-messages')
        {{-- <livewire:feedback.feedback-message />
        <livewire:feedback.alert-message /> --}}




        {{-- ------------ DATA TABLE CONTROLS ------------ --}}
        <div class="container ms-0 mt-4 mb-0">
            <livewire:data-tables.data-table-control :controls="$controls" :columns="$columns" :hiddenFields="$hiddenFields"
                :visibleColumns="$visibleColumns" :model="$model" :fieldDefinitions="$fieldDefinitions" :multiSelectFormFields="$multiSelectFormFields" :sortField="$sortField"
                :sortDirection="$sortDirection" :perPage="$perPage" :moduleName="$moduleName" :modelName="$modelName"  :recordName="$recordName" />
        </div>


        {{-- ------------ DATA TABLE  ------------ --}}
        <livewire:data-tables.data-table :fieldDefinitions="$fieldDefinitions" :hiddenFields="$hiddenFields" :multiSelectFormFields="$multiSelectFormFields"
            :queryFilters="$queryFilters"
            :columns="$columns" :model="$model" :simpleActions="$simpleActions" :controls="$controls" :visibleColumns="$visibleColumns"
            :sortField="$sortField" :sortDirection="$sortDirection" :perPage="$perPage" :moduleName="$moduleName" :modelName="$modelName"  :recordName="$recordName"
            :moreActions="$moreActions" />


        {{-- NONE REACTIVE BLADE FILE FOR SHOWING ITEM DETAIL --}}
        @include('core.views::data-tables.modals.show-detail-modal', ['selectedItem' => $selectedItem])


    </div>


</section>

@include('core.assets::data-tables.assets')
@include('core.assets::data-tables.scripts')


