<?php

return [
  'model' => 'App\\Modules\\User\\Models\\UserStatusCategory',
  'fieldDefinitions' =>  [
    'userStatuses' =>    [
      'field_type' => 'checkbox',
      'relationship' =>      [
        'model' => 'App\\Modules\\User\\Models\\UserStatus',
        'type' => 'hasMany',
        'display_field' => 'name',
        'hintField' => NULL,
        'dynamic_property' => 'userStatuses',
        'foreign_key' => 'user_status_category_id',
        'local_key' => 'id',
        'inlineAdd' => false,
      ], 

      'options' =>      [
        'model' => 'App\\Modules\\User\\Models\\UserStatus',
        'column' => 'name',
        'hintField' => NULL,
      ], 

      'label' => 'User Statuses',
      'multiSelect' => true,
    ], 

    'name' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'validation' => 'required|unique:user_status_categories,name',
      'label' => 'Name',
    ], 

    'description' =>    [
      'display' => 'inline',
      'field_type' => 'textarea',
      'label' => 'Description',
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
      'title' => 'Category Information',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'name',
        1 => 'description',
        2 => 'editable',
      ], 

    ], 

  ], 

  'moreActions' =>  [
  ], 

  'report' =>  [
  ], 

];
