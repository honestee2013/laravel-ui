@php

    $config = DataTableConfig::getConfigFileFields($model);

    $modalTitle = isset($recordName) ? $recordName : $modelName;
    $modalTitle = Str::snake($modalTitle); // Convert to snake case
    $modalTitle = ucwords(str_replace('_', ' ', $modalTitle)); // Convert to capitalised words


    $newModalTitle = $config["newModalTitle"]?? null;
    $editModalTitle = $config["editModalTitle"]?? null;
    $detailModalTitle = $config["detailModalTitle"]?? null;


    if (!isset($newModalTitle)) {
        $newModalTitle = 'New '.ucfirst($modalTitle);
    }

    if (!isset($editModalTitle)) {
        $editModalTitle = 'Edit '.ucfirst($modalTitle);
    }

    if (!isset($detailModalTitle)) {
        $detailModalTitle = ucfirst($modalTitle).' Detail';
    }

@endphp

<div wire:ignore.self id="{{$modalId}}" class="modal-wrapper" wire:key="modal-header-{{$modalId}}">
    <!-- Modal Backdrop -->
    <div class="modal-backdrop" id="modalBackdrop"
        onclick="Livewire.dispatch('close-modal-event', [{'modalId': '{{$modalId}}' }])"></div>

    <!-- Modal Content -->
    <div class="modal-content  pb-0  {{ $modalClass?? 'mainModal'}}" id="modalContent">
        <h5 class="card-title text-info text-gradient font-weight-bolder p-4 mx-4 mt-2 mb-2 pb-2">
            @if ($modalId !== "detail")
                {{ $isEditMode ? $editModalTitle : $newModalTitle }}
            @else
                {{ $detailModalTitle }}
            @endif
        </h5>
        <div class="mb-4"><hr class="horizontal dark my-0" /></div>
        <div class="modal-body">





