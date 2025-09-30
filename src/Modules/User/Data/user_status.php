<?php

return [
  'model' => 'App\\Modules\\User\\Models\\UserStatus',
  'fieldDefinitions' =>  [
    'name' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'validation' => 'required|unique:user_statuses,name',
      'label' => 'Name',
    ], 

    'description' =>    [
      'display' => 'inline',
      'field_type' => 'textarea',
      'label' => 'Description',
    ], 

    'user_status_category_id' =>    [
      'relationship' =>      [
        'model' => 'App\\Modules\\User\\Models\\UserStatusCategory',
        'type' => 'belongsTo',
        'display_field' => 'name',
        'dynamic_property' => 'userStatusCategory',
        'foreign_key' => 'user_status_category_id',
        'inlineAdd' => false,
      ], 

      'options' =>      [
        'model' => 'App\\Modules\\User\\Models\\UserStatusCategory',
        'column' => 'name',
        'hintField' => NULL,
      ], 

      'display' => 'inline',
      'field_type' => 'select',
      'label' => 'User Status Category',
    ], 

    'editable' =>    [
      'display' => 'inline',
      'field_type' => 'select',
      'options' =>      [
        'Yes' => 'Yes',
        ' No' => ' No',
      ], 

      'label' => 'Editable',
    ], 

  ], 

  'hiddenFields' =>  [
    'onTable' =>    [
    ], 

    'onNewForm' =>    [
    ], 

    'onEditForm' =>    [
    ], 

    'onQuery' =>    [
    ], 

  ], 

  'simpleActions' =>  [
    0 => 'show',
    1 => 'edit',
    2 => 'delete',
  ], 

  'isTransaction' => false,
  'dispatchEvents' => false,
  'controls' => 'all',
  'fieldGroups' =>  [
    0 =>    [
      'title' => 'User Status Information',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'name',
        1 => 'description',
        2 => 'user_status_category_id',
        3 => 'editable',
      ], 

    ], 

  ], 

  'moreActions' =>  [
  ], 

  'report' =>  [
  ], 

];
