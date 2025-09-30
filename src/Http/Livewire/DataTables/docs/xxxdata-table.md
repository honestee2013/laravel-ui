# Livewire DataTable Component

This is a reusable DataTable component built with Livewire for Laravel applications. It allows for customizable table fields, actions, and validation rules, supporting features like file uploads, select inputs, and text fields.

## Features
- **Dynamic Fields**: Add and configure different field types (text, select, textarea, file).
- **Custom Validation**: Define validation rules for each field.
- **File Uploads**: Supports file and image uploads with custom validation.
- **Actions**: Simple and advanced actions for each row, such as `edit`, `show`, `delete`, etc.
- **Hidden Fields**: Control visibility of fields on the table, form, and detail views.

## Usage

### Basic Example

```php
<livewire:data-table
    model="App\Models\User"
    :fieldDefinitions="[
        'photo' => [
            'field_type' => 'file',
            'validation' => 'nullable|image|max:1024|mimes:jpg,png,jpeg',
        ],
        'name' => ['field_type' => 'text', 'validation' => 'required|string|min:10'],
        'email' => ['field_type' => 'email', 'validation' => 'required|email'],
        'password' => 'password',
        'about_me' => 'textarea',
        'location' => [
            'field_type' => 'select',
            'options' => ['Kano' => 'Kano', 'Kaduna' => 'Kaduna', 'Abuja' => 'Abuja'],
            'multiSelect' => true,
            'validation' => 'required|min:2'
        ]
    ]"
    :simpleActions="['edit', 'delete']"
    :moreActions="[
        'show' => [
            'title' => 'View Details',
            'icon' => 'fas fa-eye text-primary',
            'route' => 'users.show',
        ]
    ]"
    :hiddenFields="[
        'onTable' => ['password', 'password_confirmation'],
        'onEditForm' => ['password_confirmation'],
    ]"

    :queryFilters = [
        ['age', '>', '18']
    ]
/>
