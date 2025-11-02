{{-- resources/views/components/data-table-actions.blade.php --}}
@props(['row', 'model', 'modelName', 'simpleActions', 'moreActions'])

@if ($simpleActions)
    @foreach ($simpleActions as $action)
        @if (strtolower($action) == 'edit')
            <span wire:click="editRecord({{ $row->id }}, '{{ addslashes($model) }}')"
                class="mx-2" style="cursor: pointer"
                data-bs-toggle="tooltip" data-bs-original-title="Edit">
                <i class="fas fa-edit text-primary"></i>
            </span>
        @elseif(strtolower($action) == 'show')
            <span wire:click="$dispatch('openDetailModalEvent', [{{ $row->id }}, '{{ addslashes($model) }}'])"
                style="cursor: pointer" class="mx-2"
                data-bs-toggle="tooltip" data-bs-original-title="Detail">
                <i class="fas fa-eye text-info"></i>
            </span>
        @elseif(strtolower($action) == 'delete')
            <span wire:click="deleteRecord({{ $row->id }})"
                class="mx-2" style="cursor: pointer"
                data-bs-toggle="tooltip" data-bs-original-title="Delete">
                <i class="fas fa-trash text-danger"></i>
            </span>
        @else
            <a href="{{ route(strtolower(Str::plural($modelName)) . '.' . strtolower(Str::singular($modelName)) . '.' . strtolower(Str::singular($action)), [strtolower($modelName) => $row->id]) }}"
                class="mx-2" data-bs-toggle="tooltip"
                style="cursor: pointer"
                data-bs-original-title="{{ucfirst($action)}}">
                <span class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                    {{ ucfirst($action) }}
                </span>
            </a>
        @endif
    @endforeach
@endif

@if ($moreActions)
    <span class="btn-group dropdown" data-bs-toggle="tooltip" data-bs-original-title="More" style="margin-right: 1.8em">
        <span class="px-2" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-vertical text-secondary" style="cursor: pointer"></i>
        </span>
        <ul class="dropdown-menu dropdown-menu-end me-sm-n4 px-2 py-3" aria-labelledby="dropdownMenuButton">
            @foreach ($moreActions as $key => $value)
                @php
                    $isGrouped = is_string($key) && is_array($value);
                    $actions = $isGrouped ? $value : [$value];
                @endphp

                @if($isGrouped)
                    <span class="m-2 text-uppercase text-xs fw-bolder">{{ ucfirst($key) }}</span>
                    <hr class="m-2 p-0 bg-gray-500" />
                @endif

                @foreach ($actions as $action)
                    @php
                        // replace {id} in $action['params']
                        $action['params'] = array_map(function($param) use ($row) {
                            return $param === '{id}' ? $row->id : $param;
                        }, $action['params'] ?? []);
                    @endphp

                    <li class="mb-2">
                        @if(isset($action['route']))
                           {{-- <a class="dropdown-item border-radius-md" wire:click="openLink('{{ $action['route'] }}', {{ json_encode(array_merge($action['params'] ?? [], ['id' => $row->id])) }})"> --}}
                           <a class="dropdown-item border-radius-md" wire:click="openLink('{{ $action['route'] }}', {{ json_encode($action['params'] ?? []) }})">
                        @elseif(isset($action['updateModelField']) && isset($action['fieldName']) && isset($action['fieldValue']) && isset($action['actionName']))
                            <a class="dropdown-item border-radius-md" onclick="Livewire.dispatch('updateModelFieldEvent',['{{$row->id}}', '{{$action['fieldName']}}', '{{$action['fieldValue']}}', '{{$action['actionName']}}', '{{$action['handleByEventHandlerOnly']?? false}}'])">
                        @elseif(isset($action['dispatchEvent']) && isset($action['eventName']) && isset($action['params']))

                            <a class="dropdown-item border-radius-md" onclick="Livewire.dispatch('{{$action['eventName']}}', [{{ json_encode($action['params']) }}] )">
                        
                        @else
                            <a class="dropdown-item border-radius-md" href="javascript:void(0)">
                        @endif

                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }}" style="margin-right: 1em"></i>
                            @endif
                            <span class="btn-inner--text">{{ $action['title'] ?? '' }}</span>
                            </a>
                    </li>

                    @if(isset($action['hr']))
                        <hr class="m-2 p-0 bg-gray-500" />
                    @endif
                @endforeach
            @endforeach
        </ul>
    </span>
@endif