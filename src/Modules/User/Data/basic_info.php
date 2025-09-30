<?php

return [
  'model' => 'App\\Modules\\User\\Models\\BasicInfo',
  'fieldDefinitions' =>  [
    'profile_picture' =>    [
      'display' => 'inline',
      'field_type' => 'file',
      'label' => 'Profile Picture',
    ], 

    'user_id' =>    [
      'relationship' =>      [
        'model' => 'App\\Models\\User',
        'type' => 'belongsTo',
        'display_field' => 'name',
        'dynamic_property' => 'user',
        'foreign_key' => 'user_id',
        'inlineAdd' => false,
      ], 

      'options' =>      [
        'model' => 'App\\Models\\User',
        'column' => 'name',
        'hintField' => NULL,
      ], 

      'display' => 'inline',
      'field_type' => 'select',
      'validation' => 'required|unique:basic_infos,user_id',
      'label' => 'User',
    ], 

    'about_me' =>    [
      'display' => 'inline',
      'field_type' => 'textarea',
      'label' => 'About Me',
    ], 

    'phone_number' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Phone Number',
    ], 

    'email' =>    [
      'display' => 'inline',
      'field_type' => 'email',
      'label' => 'Email',
    ], 

    'address_line_1' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Address Line 1',
    ], 

    'address_line_2' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Address Line 2',
    ], 

    'city' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'City',
    ], 

    'state' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'State',
    ], 

    'postal_code' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Postal Code',
    ], 

    'country' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Country',
    ], 

    'date_of_birth' =>    [
      'display' => 'inline',
      'field_type' => 'datepicker',
      'label' => 'Date Of Birth',
    ], 

    'gender' =>    [
      'display' => 'inline',
      'field_type' => 'select',
      'options' =>      [
        'Male' => 'Male',
        ' Female' => ' Female',
        ' Other' => ' Other',
      ], 

      'label' => 'Gender',
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
      'title' => 'Personal Details',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'profile_picture',
        1 => 'user_id',
        2 => 'about_me',
        3 => 'date_of_birth',
        4 => 'gender',
      ], 

    ], 

    1 =>    [
      'title' => 'Contact Information',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'phone_number',
        1 => 'email',
      ], 

    ], 

    2 =>    [
      'title' => 'Address',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'address_line_1',
        1 => 'address_line_2',
        2 => 'city',
        3 => 'state',
        4 => 'postal_code',
        5 => 'country',
      ], 

    ], 

  ], 

  'moreActions' =>  [
  ], 

  'report' =>  [
  ], 

];
