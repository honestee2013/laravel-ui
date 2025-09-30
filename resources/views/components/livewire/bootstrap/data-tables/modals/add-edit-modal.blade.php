{{-- resources/views/components/data-tables/modals/add-edit-modal.blade.php --}}
@props([
    'modalId', 'isEditMode', 'pageTitle', 'queryFilters', 'configFileName', 'config', 
    'readOnlyFields', 'fieldGroups', 'fieldDefinitions', 'model', 'moduleName', 
    'modelName', 'recordName', 'multiSelectFormFields', 'singleSelectFormFields', 
    'hiddenFields', 'columns'
])


@include('qf::components.livewire.bootstrap.data-tables.modals.modal-header', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
])
<div class="card-body">
    <livewire:qf::data-tables.data-table-form
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
        key="addEditModal"
    />
</div>
@include('qf::components.livewire.bootstrap.data-tables.modals.modal-footer', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
])