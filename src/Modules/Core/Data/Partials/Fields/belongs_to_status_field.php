<?php

return [

    'status_id' => [
        'field_type' => 'select',
        'options' => [
            'model' => 'App\Modules\Core\Models\Status',
            'column' => 'name',
        ],
        'relationship' => [
            'model' => 'App\Modules\Core\Models\Status',
            'type' => 'belongsTo',
            'display_field' => 'name',
            'dynamic_property' => 'status',
            'foreign_key' => 'status_id',
            'inlineAdd' => true,
        ],
        'label' => 'Status',
        'validation' => 'nullable|int',
        'display' => 'block',
        'multiSelect' => false,
    ],
];
