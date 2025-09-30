<?php

return [


        "model" => "App\\Modules\\Inventory\\Models\\Item",

        "fieldDefinitions" => [
            'name' => ['field_type' => 'name', 'validation' => 'required|string|min:10'],
            'photo' => [
                'field_type' => 'file',
                'validation' => 'nullable|image|max:1024|mimes:jpg,png,jpeg', // 1MB max, only jpg, png, pdf
            ],
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'password',
            'about_me' => 'textarea',
            'location' => [
                'field_type' => 'checkbox',
                'options' => ['Kano' => 'Kano', 'Kaduna' => 'Kaduna', 'Abuja' => 'Abuja'],
                'selected' => ['Abuja'],
                'multiSelect' => true,
                'display' => 'inline',
                'validation' => 'required|min:2',
                'inlineAdd' => true,
                'model' => 'App\\Modules\\Task',
                'module' => "Core",
            ]
        ],

        "hiddenFields" => [
            'onTable' => ['password', 'password_confirmation'],
            'onDetail' => ['password', 'password_confirmation'],
            'onEditForm' => ['password_confirmation'],
            'onQuery' => ['password_confirmation'],
        ],

        "simpleActions" => ['show', 'edit', 'delete'],

        "moreActions" => [
            'edit' => [
                'title' => 'Some Title',
                'icon' => 'fas fa-filel text-sm me-1 text-primary',
                'route' => 'users.user.show',
                'hr' => true,
            ],
            'show' => [],
            'delete' => []
        ],

        "controls" => "all"




];
