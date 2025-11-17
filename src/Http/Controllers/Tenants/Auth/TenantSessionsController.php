<?php

namespace QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TenantSessionsController extends Controller
{
    
    public function create()
    {
        return view('session.login-session');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required' 
        ]);

        \Log::info('Tenant Login Attempt', [
            'email' => $attributes['email'],
            'tenant_id' => tenant('id'),
            'current_database' => DB::connection()->getDatabaseName(),
            'host' => request()->getHost()
        ]);

        // Debug: Check if user exists in tenant database
        $user = \App\Models\User::where('email', $attributes['email'])->first();
        
        if ($user) {
            \Log::info('User found in tenant database', [
                'user_id' => $user->id,
                'email' => $user->email,
                'password_hash' => $user->password,
                'company_id' => $user->company_id
            ]);

            // Manual password verification
            $passwordMatch = Hash::check($attributes['password'], $user->password);
            \Log::info('Manual password check', [
                'password_match' => $passwordMatch,
                'input_password' => $attributes['password']
            ]);

        } else {
            \Log::warning('User NOT found in tenant database', [
                'email' => $attributes['email'],
                'database' => DB::connection()->getDatabaseName()
            ]);
        }

        if (Auth::attempt($attributes)) {
            session()->regenerate();
            
            \Log::info('Tenant Login Successful', [
                'user_id' => Auth::id(),
                'tenant_id' => tenant('id')
            ]);
            
            return redirect('/hr/dashboard')->with(['success'=>'You are logged in.']);
        } else {
            \Log::error('Tenant Login Failed', [
                'email' => $attributes['email'],
                'tenant_id' => tenant('id'),
                'user_exists' => !is_null($user),
                'database' => DB::connection()->getDatabaseName()
            ]);
            
            return back()->withErrors(['email'=>'Email or password invalid.']);
        }
    }
    
public function destroy()
{
    $currentDomain = request()->getHost();
    Auth::logout();
    return redirect("http://{$currentDomain}/login")->with(['success'=>'You\'ve been logged out.']);
}


}