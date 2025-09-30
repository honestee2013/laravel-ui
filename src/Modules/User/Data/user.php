<?php

return [
  'model' => 'App\\Modules\\User\\Models\\User',
  'fieldDefinitions' =>  [
    'name' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'validation' => 'required',
      'label' => 'Name',
    ], 

    'email' =>    [
      'display' => 'inline',
      'field_type' => 'email',
      'validation' => 'required|email|unique:users,email',
      'label' => 'Email',
    ], 

    'email_verified_at' =>    [
      'display' => 'inline',
      'field_type' => 'datetime',
      'label' => 'Email Verified At',
    ], 

    'password' =>    [
      'display' => 'inline',
      'field_type' => 'password',
      'validation' => 'required|min:8|confirmed',
      'label' => 'Password',
    ], 

    'password_confirmation' =>    [
      'display' => 'inline',
      'field_type' => 'password',
      'label' => 'Confirm Password',
    ], 

    'remember_token' =>    [
      'display' => 'inline',
      'field_type' => 'string',
      'label' => 'Remember Token',
    ], 

    'user_type' =>    [
      'display' => 'inline',
      'field_type' => 'select',
      'validation' => 'required|string:max:255',
      'options' =>      [
        'Staff' => 'Staff',
        ' Customer' => ' Customer',
        ' Supplier' => ' Supplier',
      ], 

      'label' => 'User Type',
    ], 

  ], 

  'hiddenFields' =>  [
    'onTable' =>    [
      0 => 'password',
      1 => 'remember_token',
      2 => 'email_verified_at',
      3 => 'password_confirmation',
    ], 

    'onNewForm' =>    [
      0 => 'email_verified_at',
      1 => 'remember_token',
    ], 

    'onEditForm' =>    [
      0 => 'email_verified_at',
      1 => 'remember_token',
    ], 

    'onQuery' =>    [
      0 => 'password',
      1 => 'remember_token',
      2 => 'password_confirmation',
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
      'title' => 'Basic Information',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'name',
        1 => 'email',
        2 => 'user_type',
      ], 

    ], 

    1 =>    [
      'title' => 'Password',
      'groupType' => 'hr',
      'fields' =>      [
        0 => 'password',
        1 => 'password_confirmation',
      ], 

    ], 

  ], 

  'moreActions' =>  [
  ], 

  'report' =>  [
  ], 

];
