<div >

    {{--<form role="form text-left" wire:submit.prevent="$dispatch('submitDatatableFormEvent')">--}}
    <form role="form text-left" class="p-4 modal-form row" >

        @foreach($fieldGroups as $key => $group)
            @php
                $groupTitle = $group['title'] ?? '';
                $groupType = $group['groupType'] ?? '';
                //$display = $group['display'] ?? 'block';
                $fields = $group['fields'] ?? [];

                if ($isEditMode )
                    $isGroupEmpty = empty(array_diff($fields, $hiddenFields['onEditForm']));
                else
                    $isGroupEmpty = empty(array_diff($fields, $hiddenFields['onNewForm']));
            @endphp


            {{--------- Ensure the group is not empty -----------}}
            @if($groupType == "hr" && !$isGroupEmpty)
                <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-1">{{$groupTitle?? ''}}</h6>
                <hr class="horizontal dark mt-0" />

            {{--------- Ensure the group is not empty -----------}}
            @elseif ($groupType == "collapsible" && !$isGroupEmpty)
                    <!-- Card for Optional Fields -->

                    <div class="mt-5 mb-1 bg-gray-100 py-2 cursor-pointer p-1 px-3 d-flex justify-content-between rounded rounded-pill
                        " data-bs-toggle="collapse" data-bs-target="#optionalFields" aria-expanded="false" aria-controls="optionalFields">
                            <span class="text-uppercase text-secondary text-xs font-weight-bolder  mb-1">Advance</span>
                            <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="bg-gray-100 mb-5 rounded  rounded-3">

                        <div class="collapse" id="optionalFields" wire:ignore.self>
                            <div class="card-body p-md-4">
            @endif

            {{--------- Group fields Rendering -----------}}
            @foreach ($fields as $field)

                <!----  CHECKING IF SHOULD BE DISPLAYED ON FORM    ---->
                @if (($isEditMode && !in_array($field, $hiddenFields['onEditForm'])) ||
                        (!$isEditMode && !in_array($field, $hiddenFields['onNewForm'])))

                        @php

                            $type = $fieldDefinitions[$field]?? 'text';
                            $options = [];

                            $reactivity = $fieldDefinitions[$field]['reactivity']?? "defer";
                            $autoGenerate = $fieldDefinitions[$field]['autoGenerate']?? false;


                            if (is_array($type) && isset($type['field_type'])) {
                                if (isset($type['options'])) {
                                    if (isset($type['options']['model'], $type['options']['column'])) {
                                        $options = DataTableOption::getOptionList($type['options']);

                                    } else {
                                        $options = $type['options'];
                                    }

                                } // Get option before updating '$type'

                                if (isset($type['selected'])) {
                                    $selected = $type['selected'];
                                } // Get selected before updating '$type'
                                if (isset($type['display'])) {
                                    $display = $type['display'];
                                } // Get display before updating '$type'

                                $type = $type['field_type'];

                                // Handle multi definition fields eg select, chckbox, radio
                            } elseif (is_array($type)) {
                                // Extracting only the 'fieldName' from the array.
                                // THIS MAY NEED MODIFICATION WHEN OTHER ARRAY INFO ARE NEEDED
                                $type = $type['field_type'];
                            }

                        @endphp



                        <!----  FORM FIELDS    ---->
                    <div class=" {{ $fieldDefinitions[$field]['wraperCssClasses']?? 'col-12'}}">
                        <div class="form-group">
                            {{-------- LABEL --------}}
                            @if (isset($fieldDefinitions[$field]['label']))
                                <label class="mt-2 mb-1" for="{{ $field }}">{{ ucwords($fieldDefinitions[$field]['label']) }}</label>
                            @else
                                <label class="mt-2 mb-1" for="{{ $field }}">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                            @endif


                            <!---- Add  item ---->
                            @if (isset($fieldDefinitions[$field]['relationship'])
                                    && isset($fieldDefinitions[$field]['relationship']['inlineAdd'])
                                    && $fieldDefinitions[$field]['relationship']['inlineAdd']
                                )
                                    <!-- Button to open the secondary modal to add new items -->
                                    <span role="button" class="badge rounded-pill bg-primary text-xxs"
                                        onclick="Livewire.dispatch('openAddRelationshipItemModalEvent',
                                            [{{ json_encode($fieldDefinitions[$field]['relationship']['model']) }}
                                            ] )">
                                        Add
                                    </span>
                            @endif


                            @if ($type === 'textarea')
                                <textarea wire:model.{{$reactivity}}="fields.{{ $field }}" id="{{ $field }}" class="form-control"
                                    name = "{{ $field }}" value= "{{ $field }}" rows="3" @if(in_array($field, $readOnlyFields)) disabled @endif>{{ $fields[$field] ?? '' }}</textarea>



                            @elseif ($type === 'select')
                                <!--------- Opening the Select Element -------->

                                @if ($field && $multiSelectFormFields && in_array($field, array_keys($multiSelectFormFields)))
                                    <select wire:key="multi-select-{{$field}}" multiple wire:model.{{$reactivity}}="multiSelectFormFields.{{ $field }}"
                                        name = "{{ $field }}" value= "{{ $field }}" id="{{ $field }}"
                                        class="form-control"  @if(in_array($field, $readOnlyFields)) disabled @endif>

                                @elseif ($field && $singleSelectFormFields && in_array($field, array_keys($singleSelectFormFields)))
                                    {{--- Single Select Dropwdown box---}}
                                    <select wire:key="single-select-{{$field}}" wire:model.{{$reactivity}}="singleSelectFormFields.{{ $field }}"
                                    name = "{{ $field }}"  id="{{ $field }}"
                                    class="form-control" @if(in_array($field, $readOnlyFields)) disabled @endif>

                                @else
                                        <select wire:key="select-{{$field}}" wire:model.{{$reactivity}}="fields.{{ $field }}" name = "{{ $field }}"
                                            value= "{{ $field }}" id="{{ $field }}" class="form-control"  @if(in_array($field, $readOnlyFields)) disabled @endif>
                                @endif

                                @if (isset($fieldDefinitions[$field]['label']))
                                    <option style="display:none" value="">Select {{ $fieldDefinitions[$field]['label'] }}...
                                    </option>
                                @else
                                    <option style="display:none" value="">Select {{ strtolower(str_replace('_', ' ', $field)) }}...</option>
                                @endif

                                @foreach ($options as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach

                                </select>
                                <!--------- Closing the Select Element -------->

                            @elseif ($type === 'checkbox')

                                <!--------- Checkbox on a horizontal line -------->
                                @if (isset($display) && $display == 'inline')<div>@endif
                                @foreach ($options as $key => $value)
                                    <div class="form-check"
                                        @if (isset($display) && $display == 'inline') style="display:inline-flex;" @endif>

                                        <!----- Multi-form field selection handled by the Livewire Automatically------>
                                        @if ($field && $multiSelectFormFields && in_array($field, array_keys($multiSelectFormFields)))
                                            <input wire:key="multi-check-{{$key}}" class="form-check-input" type="{{ $type }}"
                                                id="{{ $key }}"
                                                wire:model.{{$reactivity}}="multiSelectFormFields.{{ $field }}"
                                                value="{{ $key }}" {{-- The key should match the saved values --}} name="{{ $field }}"
                                                @if(in_array($field, $readOnlyFields)) disabled @endif
                                                >

                                            <!----- Normal field selection handled manually------>
                                        @else
                                            <input wire:key="check-{{$key}}" class="form-check-input" type="{{ $type }}"
                                                id="{{ $key }}" wire:model.{{$reactivity}}="fields.{{ $field }}"
                                                value="{{$key}}" {{-- Again, using $key to match the saved values --}}
                                                @if (in_array($key, $fields[$field] ?? [])) checked @endif name="{{ $field }}">
                                        @endif

                                        <label class="custom-control-label" for="{{ $key }}"
                                            @if (isset($display) && $display == 'inline') style="margin: 0.25em 2em 1em 0.5em" @endif>
                                            {{ $value }}
                                        </label>
                                    </div>
                                @endforeach
                                @if (isset($display) && $display == 'inline')</div>@endif
                                <!--------- End Checkbox on a horizontal line -------->

                            @elseif ($type === 'radio')
                                <!--------- Radio button on a horizontal line -------->
                                @if (isset($display) && $display == 'inline')<div>@endif
                                @foreach ($options as $key => $value)

                                    @if ($field && $singleSelectFormFields && in_array($field, array_keys($singleSelectFormFields)))

                                        <div class="form-check"
                                            @if (isset($display) && $display == 'inline') style="display:inline-flex;" @endif>

                                            <input wire:key="radio-{{$key}}" class="form-check-input" type="{{ $type }}"
                                                id="{{ $key }}" wire:model.{{$reactivity}}="singleSelectFormFields.{{ $field }}"
                                                value="{{ $key }}"
                                                @if(in_array($field, $readOnlyFields)) disabled @endif>



                                            <label class="custom-control-label" for="{{ $key }}"
                                            @if (isset($display) && $display == 'inline') style="margin: 0.25em 2em 1em 0.5em" @endif>
                                                {{ $value }}
                                            </label>
                                        </div>

                                    @endif

                                @endforeach
                                @if (isset($display) && $display == 'inline')</div>@endif
                                <!--------- End button on a horizontal line -------->

                            @elseif ($type === 'file' && in_array($field, DataTableConfig::getSupportedImageColumnNames()))
                                <!----------- IMAGE INPUT ------------->
                                <div class="row border rounded-3 m-1 p-3">

                                    <div class="col-9">
                                        <!--- INPUT Field ---->
                                        <input type="{{ $type }}" wire:model.{{$reactivity}}="fields.{{ $field }}" accept="image/*"
                                            id="{{ $field }}" class="form-control rounded-pill" value="{{ $fields[$field] ?? '' }}"
                                            @if(in_array($field, $readOnlyFields)) disabled @endif
                                            >

                                        <!--- INPUT Info ---->
                                        @if (isset($fields[$field]) && is_object($fields[$field]) && $fields[$field]->temporaryUrl())
                                            <span class="text-xs">This is the <strong>Selected Image</strong></span>
                                        @elseif(!empty($fields[$field]))
                                            <span class="text-xs">This is the <strong> Current Image</strong></span>
                                        @endif
                                    </div>






                                    <div class="col-2 rounded border border-red p-1 ms-3 image-container" id="image-container-{{ $field }}">

                                        <!--- IMAGE PREVIEW THUBMNAIL ---->
                                        @if (isset($this->fields[$field]))

                                            <!------ Crop Thubnail -------->
                                            <img id="image-preview-{{ $field }}" {{-- --------  Selected on the Client side  ---------- --}}
                                                @if (is_object($this->fields[$field]) && $this->fields[$field]->temporaryUrl()) src="{{ $this->fields[$field]->temporaryUrl() }}"

                                                                        {{-- --------  Already exist on the Server side  ---------- --}}
                                                                        @elseif (isset($this->fields[$field]))
                                                                            src="{{ asset('storage/' . $this->fields[$field]) }}" @endif
                                                alt="Image Preview" style="width: 100%;" />

                                            <!------ Crop Icon-------->
                                            <span
                                                @if (is_object($this->fields[$field]) && $this->fields[$field]->temporaryUrl()) wire:click="openCropImageModal( '{{ $field }}', '{{ $this->fields[$field]->temporaryUrl() }}', '{{ $this->getId() }}')"
                                                                        @else
                                                                            wire:click="openCropImageModal('{{ $field }}', '{{ asset('storage/' . $this->fields[$field]) }}', '{{ $this->getId() }}')" @endif
                                                class="mx-2" style="" data-bs-toggle="tooltip" data-bs-original-title="Crop">
                                                <span style="cursor: pointer;">
                                                    <i class="fas fa-edit text-primary"></i>
                                                    <span class="text-xs">Crop</span>
                                                </span>
                                            </span>
                                        @endif

                                    </div>
                                </div>

                            @elseif (str_contains($type, "date") || str_contains($type, "time"))

                                <!----------- DATE FIELD ------------->
                                <input type="{{ $type }}" wire:model.{{$reactivity}}="fields.{{ $field }}" id="{{ $field }}"
                                    class="form-control rounded-pill {{ $type }}"  value="{{ $fields[$field] ?? '' }}" name="{{ $field }}"
                                        placeholder="Please provide the {{strtolower(str_replace('_', ' ', $field))}}..."
                                        @if(in_array($field, $readOnlyFields)) disabled @endif
                                    >


                            @else
                                <!----------- ANY OTHER INPUT ------------->
                                <div class="input-group">
                                <input type="{{ $type }}" wire:model.{{$reactivity}}="fields.{{ $field }}" id="{{ $field }}"
                                    class="form-control" value="{{ $fields[$field] ?? '' }}" name="{{ $field }}"
                                        placeholder="Please provide the {{strtolower(str_replace('_', ' ', $field))}}..."

                                        {{--- Auto Generate Code Field--}}
                                        @if($autoGenerate) wire:focus="generateOrderNumber('{{addslashes($model)}}', '{{ $modelName }}',  '{{ $field }}')" @endif
                                        @if(in_array($field, $readOnlyFields)) disabled @endif
                                    >

                                    {{--- Auto Generate Code Field--}}
                                    @if($autoGenerate)
                                        <button class="btn btn-outline-primary mb-0" type="button" id="button-addon2"
                                            wire:click="generateOrderNumber('{{addslashes($model)}}', '{{ $modelName }}',  '{{ $field }}')"
                                        ><i class="fas fa-sync-alt me-1"></i> Auto</button>
                                    @endif
                                </div>
                            @endif

                            <!----------- Validation Error  ------------->
                            @error('fields.' . $field)
                                @php $message = str_replace('characters.', '', $message) @endphp
                                @php $message = str_replace('id', ' ', $message) @endphp
                                <span class="text-danger text-sm mb-0"> {{ str_replace('fields.', ' ', $message) }} </span>
                            @enderror

                            @error('multiSelectFormFields.' . $field)
                                @php $message = str_replace('characters.', '', $message) @endphp
                                @php $message = str_replace('id', ' ', $message) @endphp
                                <span class="text-danger text-sm mb-0"> {{ str_replace('multi select form fields.', ' ', $message) }} </span>
                            @enderror

                            @error('singleSelectFormFields.' . $field)
                                @php $message = str_replace('characters.', '', $message) @endphp
                                @php $message = str_replace('id', ' ', $message) @endphp
                                <span class="text-danger text-sm mb-0"> {{ str_replace('single select form fields.', ' ', $message) }} </span>
                            @enderror

                        </div>
                    </div>

                <!----  END CHECKING IF SHOULD BE DISPLAYED ON FORM    ---->
                @endif
            @endforeach



            @if($groupType == "hr")
                <div class="mt-5 col-12"></div>

            @elseif ($groupType == "collapsible")
                            </div> {{---Card Body ends-----}}
                        </div> {{---Colapsible group Ends ---}}
                    </div> {{--Card ends ---}}
            @endif





        @endforeach
    </form>

    @include('core.views::data-tables.partials.form-footer', [
        'modalId' => $modalId,
    ])

</div>

