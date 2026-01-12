{{-- resources/views/components/data-table-actions.blade.php --}}
@props(['row', 'model', 'modelName', 'simpleActions', 'moreActions'])
@inject('authHelper', 'App\Modules\Admin\Services\AuthorizationService')

@php
    // Get current authenticated user
    $user = auth()->user();
    // Determine the employee/owner of this row for scope checks
    $rowEmployeeId = $row->employee_id ?? $row->id; // Adjust based on your model
@endphp

@if ($simpleActions)
    @foreach ($simpleActions as $action)
        @php
            // Check if user has basic permission for this model
            $canPerformSimpleAction = true;
            $permissionName = $action."_".strtolower(Str::snake($modelName));
            if ($action == "show")
                $permissionName = "view_".strtolower(Str::snake($modelName));
            

        @endphp
        
        @if ($user->can($permissionName))
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
                @php
                    // Check permission for custom action (e.g., 'approve', 'reject')
                    $permissionName = strtolower($modelName) . '.' . strtolower($action);
                @endphp
                @if ($user->can($permissionName))
                    <a href="{{ route(strtolower(Str::plural($modelName)) . '.' . strtolower(Str::singular($modelName)) . '.' . strtolower(Str::singular($action)), [strtolower($modelName) => $row->id]) }}"
                        class="mx-2" data-bs-toggle="tooltip"
                        style="cursor: pointer"
                        data-bs-original-title="{{ucfirst($action)}}">
                        <span class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                            {{ ucfirst($action) }}
                        </span>
                    </a>
                @endif
            @endif
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
                    {{-- Check if any action in group is visible --}}
                    @php
                        $hasVisibleActionsInGroup = false;
                        foreach ($actions as $groupAction) {
                            if ($authHelper->canPerformAction($user, $groupAction, $row)) {
                                $hasVisibleActionsInGroup = true;
                                break;
                            }
                        }
                    @endphp
                    
                    @if ($hasVisibleActionsInGroup)
                        <span class="m-2 text-uppercase text-xs fw-bolder">{{ ucfirst($key) }}</span>
                        <hr class="m-2 p-0 bg-gray-500" />
                    @endif
                @endif

                @foreach ($actions as $action)
                    @php
                    
                        // Check if user can perform this action on this specific row
                        if (!$authHelper->canPerformAction($user, $action, $row)) {
                            continue; // Skip this action
                        }
                        
                        // Replace {id} in $action['params']
                        $action['params'] = array_map(function($param) use ($row) {
                            return $param === '{id}' ? $row->id : $param;
                        }, $action['params'] ?? []);


                    @endphp

<li class="mb-2">
    @if(isset($action['route']))
        @php 
            $fullRoute = route($action['route'], $action['params'] ?? []); 
        @endphp
        
        @if(isset($action['newTab']) && $action['newTab'])
            {{-- Frontend only: Open route in new tab --}}
            <a class="dropdown-item border-radius-md" href="{{ $fullRoute }}" target="_blank">
        @else
            {{-- Backend: Process via Livewire --}}
            <a class="dropdown-item border-radius-md" wire:click="openRoute('{{ $action['route'] }}', {{ json_encode($action['params'] ?? []) }})">
        @endif

    @elseif(isset($action['url']))
        @php 
            $queryParams = !empty($action['params']) ? '?' . http_build_query($action['params']) : '';
            $fullUrl = $action['url'] . $queryParams;
        @endphp

        @if(isset($action['newTab']) && $action['newTab'])
            {{-- Frontend only: Open URL in new tab --}}
            <a class="dropdown-item border-radius-md" href="{{ $fullUrl }}" target="_blank">
        @else
            {{-- Backend: Process via Livewire --}}
            <a class="dropdown-item border-radius-md" wire:click="openUrl('{{ $action['url'] }}', {{ json_encode($action['params'] ?? []) }})">
        @endif

    @elseif(isset($action['updateModelField']) && isset($action['fieldName']) && isset($action['fieldValue']) && isset($action['actionName']))
        @php
            $fieldVal = is_bool($action['fieldValue']) ? (int) $action['fieldValue'] : $action['fieldValue'];
        @endphp
        <a class="dropdown-item border-radius-md" onclick="Livewire.dispatch('updateModelFieldEvent',['{{$row->id}}', '{{$action['fieldName']}}', '{{$fieldVal}}', '{{$action['actionName']}}', '{{$action['handleByEventHandlerOnly'] ?? false}}'])">
    
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