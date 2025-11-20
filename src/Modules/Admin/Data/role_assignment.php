<?php

return [


        "model" => "App\\Models\\User",

        "fieldDefinitions" => [
            'name' => ['field_type' => 'text', 'validation' => 'required|string|max:255'],
            'email' => ['field_type' => 'email', 'validation' => 'required|email|max:255|unique:users,email'],


            "password" => ['field_type' => 'password', "validation" => "required|string|max:255|min:8|confirmed"],
            "password_confirmation" => ['field_type' => 'password'],


            'roles' => [
                'field_type' => 'checkbox',
                'options' => [
                    'model' => 'App\Modules\Admin\Models\Role',
                    'column' => 'name',
                ],
                'relationship' => [
                    'model' => 'App\Modules\Admin\Models\Role',
                    'type' => 'belongsToMany',
                    'display_field' => 'name',
                    'dynamic_property' => 'roles',
                    'multiSelect' => true,
                    'foreign_key' => 'role_id',
                    'inlineAdd' => true,
                ],
                'label' => 'User Roles',
                'display' => 'inline',
                'multiSelect' => true,
                'validation' => 'required'
            ],

        ],

        "hiddenFields" => [
            'onTable' => ['password', 'password_confirmation'],
            'onDetail' => ['password', 'password_confirmation'],
            'onEditForm' => ['name', 'email', 'password', 'password_confirmation'],
            'onNewForm' => [],
            'onQuery' => ['password_confirmation',],
            'onEditFormValidation' => ['password_confirmation'],
        ],

        "simpleActions" => ['show', 'edit'],

        "controls" => [
            'files' => [
                'export' => ['xls', 'csv', 'pdf'],
                'import' => ['xls', 'csv', ],
                'print',
            ],
            'perPage' => [5, 10, 25, 50, 100, 200, 500],

            'bulkActions' => [
                'export' => ['xls', 'csv', 'pdf'],
            ],
            'search',
            'showHideColumns',
        ],

];
