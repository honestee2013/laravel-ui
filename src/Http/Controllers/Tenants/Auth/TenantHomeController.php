<?php

namespace QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth;

use App\Http\Controllers\Controller;

class TenantHomeController extends Controller
{
    public function home()
    {
        // Redirect to tenant-specific dashboard
        return redirect('/hr/dashboard');
    }
}