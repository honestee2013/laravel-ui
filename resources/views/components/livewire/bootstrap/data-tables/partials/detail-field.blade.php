@php
    $isPasswordField = $column == 'password';
    $isRelationshipField = isset($fieldDefinitions[$column]) && isset($fieldDefinitions[$column]['relationship']);
    $isMultiSelectField = $column && isset($multiSelectFormFields) && in_array($column, array_keys($multiSelectFormFields));
@endphp

<div class="detail-field mb-4 pb-3 border-bottom">
    <div class="field-label mb-2">
        @if (isset($fieldDefinitions[$column]['label']))
            <strong class="text-dark fw-semibold">{{ ucwords($fieldDefinitions[$column]['label']) }}</strong>
        @else
            <strong class="text-dark fw-semibold">{{ ucwords(str_replace('_', ' ', $column)) }}</strong>
        @endif
    </div>
    
    <div class="field-value">
        @if ($isPasswordField)
            <div class="text-muted font-monospace bg-light rounded px-2 py-1 d-inline-block">
                ••••••••••••••••
            </div>
        @elseif($isRelationshipField)
            @if (isset($fieldDefinitions[$column]['relationship']['type']) && 
                 ($fieldDefinitions[$column]['relationship']['type'] == 'hasMany' || 
                  $fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany'))
                <div class="relationship-values">
                    @php
                        $relatedItems = $selectedItem->{$fieldDefinitions[$column]['relationship']['dynamic_property']}->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray();
                    @endphp
                    
                    @if(count($relatedItems) > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($relatedItems as $item)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted fst-italic">No related items</span>
                    @endif
                </div>
            @else
                @php
                    $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
                    $displayField = $fieldDefinitions[$column]['relationship']['display_field'];
                    $relatedItem = optional($selectedItem->{$dynamic_property})->$displayField;
                @endphp
                
                @if($relatedItem)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                        {{ $relatedItem }}
                    </span>
                @else
                    <span class="text-muted fst-italic">Not set</span>
                @endif
            @endif
        @elseif($isMultiSelectField)
            <div class="multi-select-values">
                @php
                    $values = str_replace(['[', ']', '"'], '', $selectedItem->$column);
                    $valueArray = $values ? explode(',', $values) : [];
                @endphp
                
                @if(count($valueArray) > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($valueArray as $value)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                {{ trim($value) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted fst-italic">No values selected</span>
                @endif
            </div>
        @else
            <div class="text-data">
                @if($selectedItem->$column === null || $selectedItem->$column === '')
                    <span class="text-muted fst-italic">Empty</span>
                @else
                    <span class="text-break">{{ $selectedItem->$column }}</span>
                @endif
            </div>
        @endif
    </div>
</div>