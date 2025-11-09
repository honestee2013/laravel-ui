@include('system.views::data-tables.modals.modal-header', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
    'isModal' => $isModal,
])

@include('system.views::data-tables.data-table-form')

@include('system.views::data-tables.modals.modal-footer', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
    'isModal' => $isModal,

])
