@include('core.views::data-tables.modals.modal-header', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
    'isModal' => $isModal,
])

@include('core.views::data-tables.data-table-form')

@include('core.views::data-tables.modals.modal-footer', [
    'modalId' => $modalId,
    'isEditMode' => $isEditMode,
    'isModal' => $isModal,

])
