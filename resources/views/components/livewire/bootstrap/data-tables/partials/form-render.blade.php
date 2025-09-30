
<div wire:key="form-render-{{$modalId}}">
    <livewire:data-tables.data-table-form
    :fieldDefinitions="$fieldDefinitions"
    :model="$model"
    :moduleName="$moduleName"
    :modelName="$modelName"
    :multiSelectFormFields="$multiSelectFormFields"
    :singleSelectFormFields="$singleSelectFormFields"

    :hiddenFields="$hiddenFields"
    :columns="$columns"
    :modalId="$modalId"
    :key="$modalId"
    :fieldGroups="$fieldGroups"
    :config="$config"
/>
</div>
