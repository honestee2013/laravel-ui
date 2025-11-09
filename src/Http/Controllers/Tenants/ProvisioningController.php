<?php

namespace QuickerFaster\LaravelUI\Http\Controllers\Tenants;

use App\Modules\System\Models\Company;
;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;

class ProvisioningController extends Controller
{
    public function index()
    {
        $company = tenant(); // Company instance

        // Skip if already active
        if ($company->status === 'Active') {
            return redirect()->route('onboarding');
        }

        // Step 1: Ensure DB exists and run migrations
        if (! DB::connection('tenant')->getDatabaseName()) {
            // Auto-create DB if using auto-create config
            // Or assume it was pre-created (e.g., cPanel)
        }

        // Migrate tenant DB
        Artisan::call('tenants:migrate', [
            '--tenants' => [$company->id],
            '--force' => true,
        ]);

        // Step 2: Create admin user
        $user = User::firstOrCreate(
            ['email' => $company->billing_email],
            [
                'name' => 'Admin',
                'password' => bcrypt(str_random(32)), // or force reset on first login
                'email_verified_at' => now(),
            ]
        );

        // Step 3: Assign role
        $role = Role::firstOrCreate(['name' => 'ceo']);
        $user->assignRole($role);

        // Step 4: Activate modules (conditionally run extra migrations)
        $activeModules = json_decode($company->active_modules, true) ?: ['hr'];
        foreach ($activeModules as $module) {
            if ($module === 'hr') {
                // HR migrations are included in main tenant migration
                // Or you can run module-specific seeds/migrations here if needed
            }
            // Add cases for other modules
        }

        // Step 5: Activate company
        $company->update(['status' => 'Active']);

        return redirect()->route('onboarding');
    }
}