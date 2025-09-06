To get started with the qf::layouts.app backend layout, update your web.php route file to use the new dashboard view:
php
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('qf::dashboard');
});
Use code with caution.

You can also create your own views that extend the package's layout.
resources/views/profile.blade.php:
blade
@extends('qf::layouts.app')

@section('content')
    <h1>User Profile</h1>
    <!-- ... -->
@endsection
Use code with caution.

Conclusion