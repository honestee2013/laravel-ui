<?php

namespace QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class TenantRegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted']
        ]);

        $attributes['password'] = bcrypt($attributes['password']);

        // Create user in TENANT database
        $user = User::create($attributes);
        Auth::login($user);

        session()->flash('success', 'Your account has been created.');
        
        // Redirect to tenant dashboard
        return redirect('/hr/dashboard'); // Or your tenant dashboard route
    }
}