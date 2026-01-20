<section class="m-0 m-md-4">

    {{-- ----------------- MAIN MODAL FOR ADD-EDIT ----------------- --}}

        <div class="card-body">
            {{-- REACTIVE FORM COMPONENT --}}
            
            <livewire:qf::data-tables.data-table-form
                :pageTitle="$pageTitle"
                :queryFilters="$queryFilters"
                :configFileName="$configFileName"
                :config="$config"

                :fieldGroups="$fieldGroups"
                :fieldDefinitions="$fieldDefinitions"
                :model="$model"
                :moduleName="$moduleName"
                :modelName="$modelName"
                :recordName="$recordName"
                :multiSelectFormFields="$multiSelectFormFields"
                :singleSelectFormFields="$singleSelectFormFields"
                :hiddenFields="$hiddenFields"
                :readOnlyFields="$readOnlyFields"
                :columns="$columns"
                :isEditMode="$isEditMode"
                :modalId="$modalId"
                key="addEditModal" />
        </div>






</section>



