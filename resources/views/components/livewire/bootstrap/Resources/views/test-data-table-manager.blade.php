@extends('layouts.app')

@section('auth-soft-ui')
    <livewire:data-tables.data-table-manager
        model="App\\Models\\User"
        moduleName="core"
    />
@endsection
