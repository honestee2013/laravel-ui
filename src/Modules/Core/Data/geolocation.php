<?php

return [
  'model' => 'App\\Modules\\Core\\Models\\Geolocation',
  'fieldDefinitions' =>  [
    'name' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'validation' => 'required|string|max:255',
      'label' => 'Name',
    ], 

    'address' =>    [
      'display' => 'inline',
      'field_type' => 'textarea',
      'validation' => 'required|string|max:255',
      'label' => 'Address',
    ], 

    'description' =>    [
      'display' => 'inline',
      'field_type' => 'textarea',
      'label' => 'Description',
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
      'title' => 'Geolocation Information',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'name',
        1 => 'address',
        2 => 'description',
      ], 

    ], 

  ], 

  'moreActions' =>  [
  ], 

  'report' =>  [
  ], 

];
