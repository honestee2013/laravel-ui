<?php

return array (
  0 => 
  array (
    'itemType' => 'item-separator',
    'title' => '<h6 class="ps-3 mt-4 mb-2 text-uppercase text-xs font-weight-bolder opacity-6 group-title">Users Management</h6>',
    'url' => NULL,
  ),
  1 => 
  array (
    'title' => 'Manage Users',
    'icon' => 'fas fa-users',
    'url' => 'user/users',
    'groupTitle' => 'Users Management',
    'submenu' => 
    array (
      0 => 
      array (
        'title' => 'User Overview',
        'url' => 'user/users',
      ),
      1 => 
      array (
        'title' => 'Basic Info',
        'url' => 'user/basic-infos',
      ),
    ),
  ),
  2 => 
  array (
    'title' => 'Manage User Status',
    'icon' => 'fas fa-user-tag',
    'url' => 'user/user-statuses',
    'submenu' => 
    array (
      0 => 
      array (
        'title' => 'User Status',
        'url' => 'user/user-statuses',
      ),
      1 => 
      array (
        'title' => 'Status Categories',
        'url' => 'user/user-status-categories',
      ),
    ),
  ),
);
