{{-- resources/views/livewire/wizard-step-form.blade.php --}}
<div class="wizard-step-form">

    <form role="form text-left" class="p-4">

       
@foreach($fieldGroups as $group)
    @php
        $groupTitle = $group['title'] ?? '';
        $groupType = $group['groupType'] ?? 'hr';
        $groupedFields = $group['fields'] ?? [];
    @endphp

    @if($groupType == 'hr')
        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-1">
            {{ $groupTitle }}
        </h6>
        <hr class="horizontal dark mt-0" />
    @endif

    @foreach($groupedFields as $field)
        {{-- Only render if field exists in definitions --}}
        @if(isset($fieldDefinitions[$field]))
            @php
                $binding = "formData." . $currentModelAlias . "." . $field;
                $isUserLinkField = ($field === $linkUserFieldName);
                $isDatabaseLinkField = ($field === $linkDatabaseFieldName);
                $isLinkSource = ($currentStepConfig['isLinkSource'] ?? false);
                $requiresLink = ($currentStepConfig['requiresLink'] ?? false);
                
                // Disable fields on dependent steps
                $isDisabled = false;
                if ($requiresLink) {
                    if ($isUserLinkField) {
                        $isDisabled = true;
                    } elseif ($isDatabaseLinkField) {
                        continue; // Skip rendering this field
                    }
                }

            @endphp
            <x-qf::livewire.bootstrap.data-tables.fields.data-table-field
                :field="$field"
                :fieldDefinitions="$fieldDefinitions"
                :isEditMode="true"
                :multiSelectFormFields="$multiSelectFormFields"
                :singleSelectFormFields="$singleSelectFormFields"
                
                :readOnlyFields="$isDisabled ? array_merge($readOnlyFields, [$field]) : $readOnlyFields"
               
                :model="$model"
                :modelName="$modelName"
                reactivity="live"
                :autoGenerate="$fieldDefinitions[$field]['autoGenerate'] ?? false"
                :fieldBindingPath="$binding"
            />
        @endif
    @endforeach

    @if($groupType == 'hr')
        <div class="mt-5 col-12"></div><br />
    @endif
@endforeach


    </form>
</div>