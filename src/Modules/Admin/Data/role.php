<?php

return [

        "model"=>"App\\Modules\\Admin\\Models\\Role",
        "fieldDefinitions"=>[
                'name' => [
                    'field_type' => 'text',
                    'validation' => 'required|string|max:255',
                ],
                'description' =>'textarea',

                'permissions' => [
                    'field_type' => 'checkbox',
                    'options' => [
                        'model' => 'App\Modules\Admin\Models\Permission',
                        'column' => 'name',
                    ],
                    'relationship' => [
                        'model' => 'App\Modules\Admin\Models\Permission',
                        'type' => 'belongsToMany',
                        'display_field' => 'name',
                        'dynamic_property' => 'permissions',
                        'multiSelect' => true,
                        'foreign_key' => 'permission_id',
                        'inlineAdd' => true,
                    ],
                    'label' => 'Role Permissions',
                    'display' => 'inline',
                    
                    'validation' => 'required'
                ],



                'editable' => [
                    'field_type' => 'boolean',
                    'label' => 'Editable',
                ],
        ],



        "hiddenFields"=>[
            'onTable' => [
                'permissions',
            ],
            'onNewForm' => [
                'editable',

            ],
            'onEditForm' => [
                'editable',
            ],

            'onQuery' => [

            ],
        ],


        "simpleActions"=>['show', 'edit', 'delete'],


        "controls" => [
            'addButton',
            'files' => [
                'export' => ['xls', 'csv', 'pdf'],
                'import' => ['xls', 'csv', ],
                'print',
            ],
            'perPage' => [5, 10, 25, 50, 100, 200, 500],

            'bulkActions' => [
                'export' => ['xls', 'csv', 'pdf', 'delete'],
            ],
            'search',
            'showHideColumns',
        ],

];
