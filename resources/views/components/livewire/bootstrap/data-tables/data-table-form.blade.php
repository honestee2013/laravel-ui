<div>
    <form role="form text-left" class="p-4 modal-form row">




        @if ($errors->any())
            <div class="alert alert-primary border border-danger rounded-3 shadow-sm mb-5 mt-0" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3 mt-1">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px;">
                            <i class="fas fa-exclamation"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-white mb-2 fw-bold">Please correct the following:</h6>
                        <ul class="mb-0 ps-3 text-white" style="font-size: 0.9rem; line-height: 1.5;">
                            @foreach ($errors->all() as $error)
                                <li>{{ str_replace('fields.', '', $error) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif





        @foreach ($fieldGroups as $key => $group)
            @php
                $groupTitle = $group['title'] ?? '';
                $groupType = $group['groupType'] ?? '';
                $groupedfields = $group['fields'] ?? [];

                $isGroupEmpty = $this->isGroupEmpty($groupType, $groupedfields, $hiddenFields, $isEditMode);
            @endphp

            @if (!$isGroupEmpty)
                @if ($groupType == 'hr')
                    <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-1">
                        {{ $groupTitle }}
                    </h6>
                    <hr class="horizontal dark mt-0" />
                @elseif ($groupType == 'collapsible')
                    <div class="mt-5 mb-1 bg-gray-100 py-2 cursor-pointer p-1 px-3 d-flex justify-content-between rounded rounded-pill"
                        data-bs-toggle="collapse" data-bs-target="#optionalFields" aria-expanded="false"
                        aria-controls="optionalFields">
                        <span class="text-uppercase text-secondary text-xs font-weight-bolder mb-1">
                            Advance
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="bg-gray-100 mb-5 rounded rounded-3">
                        <div class="collapse" id="optionalFields" wire:ignore.self>
                            <div class="card-body p-md-4">
                @endif

                @foreach ($groupedfields as $field)
                    @if ($this->shouldDisplayField($field, $hiddenFields, $isEditMode))
                        <x-qf::livewire.bootstrap.data-tables.fields.data-table-field :field="$field"
                            :fieldDefinitions="$fieldDefinitions" :isEditMode="$isEditMode" :multiSelectFormFields="$multiSelectFormFields" :singleSelectFormFields="$singleSelectFormFields" :readOnlyFields="$readOnlyFields"
                            :fields="$fields" :model="$model" :modelName="$modelName" :reactivity="$fieldDefinitions[$field]['reactivity'] ?? 'defer'"
                            :autoGenerate="$fieldDefinitions[$field]['autoGenerate'] ?? false" />
                    @endif
                @endforeach

                @if ($groupType == 'hr')
                    <div class="mt-5 col-12"></div>
                @elseif ($groupType == 'collapsible')
</div>
</div>
</div>
@endif
@endif
@endforeach
</form>

@include('qf::components.livewire.bootstrap.data-tables.partials.form-footer', [
    'modalId' => $modalId,
])

</div>
