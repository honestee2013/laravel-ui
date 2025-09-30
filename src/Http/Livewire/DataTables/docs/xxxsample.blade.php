




@extends('layouts.app')

@section('auth-soft-ui')



   <livewire:data-tables.data-table
    model="App\Models\User"
    :fieldDefinitions="[
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
        ],

        'tasks' => [
            'field_type' => 'checkbox',
            'options' => App\Models\Task::pluck('name', 'id')->toArray(),
            'relationship' => [
                'model' => 'App\Modules\Task',
                'type' => 'belongsToMany',
                'display_field' => 'name',
                'dynamic_property' => 'tasks',
                'foreign_key' => 'task_id',

                'label' => 'Tasks',
                'display' => 'inline',
                'multiSelect' => true,

                'inlineAdd' => true,
                'model' => 'App\\Modules\\Operation\\Models\\Task',
                'module' => 'Operation',
            ],

        ],



    ]"
    :simpleActions="['show', 'edit', 'delete']"
    :moreActions="[
        'edit' => [
            'title' => 'Some Title',
            'icon' => 'fas fa-filel text-sm me-1 text-primary',
            'route' => 'users.user.show',
            'hr' => true,
        ],
        'show' => [],
        'delete' => []
    ]"

    :hiddenFields="[
        'onTable' => ['password', 'password_confirmation'],
        'onDetail' => ['password', 'password_confirmation'],
        'onEditForm' => ['password_confirmation'],
    ]"

    :controls="[
        'addButton',
        'files' => [
            'export' => ['xls', 'csv', 'pdf'],
            'import' => ['xls', 'csv'],
            'print'
        ],

        'bulkActions' => [
            'export' => ['xls', 'csv', 'pdf'],
            'delete',
        ],

        'perPage' => [5, 10, 25, 50, 100, 200, 500],
        'search',
        'showHideColumns',
    ]"

    :queryFilters = "[
        ['age', '>', '18']
    ]"


/>



@endsection

