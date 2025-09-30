

<table>
    <thead>
        <tr>
            @foreach ($data->first()->toArray() as $column => $value)
                @if (in_array($column, $columns))
                    <th>
                        {{ $fieldDefinitions[$column]['label'] ?? ucwords(str_replace('_', ' ', $column)) }}
                    </th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                @foreach ($row->toArray() as $column => $value)
                    @if (in_array($column, $columns))
                        @php
                            $fieldDef = $fieldDefinitions[$column] ?? [];
                        @endphp

                        @if ($multiSelectFormFields && in_array($column, array_keys($multiSelectFormFields)))
                            <td>{{ str_replace(',', ', ', str_replace(['[', ']', '"'], '', $value)) }}</td>

                        @elseif (in_array($column, ['image', 'photo', 'picture']))
                            <td></td> {{-- Empty for images, Excel handles it via drawings() --}}

                        @elseif (isset($fieldDef['relationship']))
                            @php
                                $rel = $fieldDef['relationship'];
                                $type = $rel['type'] ?? 'belongsTo';
                                $dynamic_property = $rel['dynamic_property'] ?? '';
                                $displayField = $rel['display_field'] ?? '';
                            @endphp

                            @if ($type == 'hasMany' || $type == 'belongsToMany')
                                @php
                                    $relatedValues = $row->{$dynamic_property}
                                        ->pluck($displayField)
                                        ->toArray();
                                @endphp
                                <td>{{ implode(', ', $relatedValues) }}</td>
                            @else
                                <td>{{ optional($row->{$dynamic_property})->$displayField }}</td>
                            @endif

                        @else
                            <td>{{ is_array($value) ? '' : $value }}</td>
                        @endif
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
