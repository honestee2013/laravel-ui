<?php

return [

        "model"=>"App\\Modules\\Core\\Models\\Status",
        "fieldDefinitions"=>[
            'name' => ['field_type' => 'text', 'validation' => 'required|string'],
            'description' => ['field_type' => 'textarea'],
            'editable' => [
                'field_type' => 'boolean',
                'label' => 'Editable',
            ],

        ],

        "hiddenFields"=>[
            'onNewForm' => [ "editable"],
            'onEditForm' => [ "editable"],
        ],


        "simpleActions"=>['show', 'edit', 'delete'],

        "controls"=>"all",


];
