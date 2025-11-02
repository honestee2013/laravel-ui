<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Tenants;


use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Stancl\Tenancy\Database\TenantDatabaseManager;
use Stancl\Tenancy\Contracts\Tenant;

class RegisterTenant extends Component
{
    public $company_name;
    public $email;
    public $password;
    public $subdomain;
    public $isCreating = false;

    protected $reservedSubdomains = ['www', 'api', 'admin', 'mail', 'localhost', 'test'];

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'subdomain' => 'required|string|alpha_dash|min:3|max:32|unique:companies,subdomain',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function register()
    {
        $this->validate();

        // Validate subdomain format (strict)
        if (! preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $this->subdomain)) {
            throw ValidationException::withMessages([
                'subdomain' => 'Subdomain must start/end with letter/number and contain only letters, numbers, or hyphens.'
            ]);
        }

        // Avoid www, admin etc.
        $this->validateSubdomain();


        

        $this->isCreating = true;
        $this->dispatchBrowserEvent('show-preparing');

        try {
            // Step 1: Create central Company
            $company = Company::create([
                'name' => $this->company_name,
                'subdomain' => $this->subdomain,
            ]);

            // Step 2: Generate neutral DB name
            $databaseName = 'saas_' . $this->subdomain;

            // Step 3: Provision DB (cPanel or fallback)
            $this->provisionDatabase($databaseName);

            // Step 4: Create tenant
            $tenant = Tenant::create([
                'id' => $this->subdomain,
                'database' => $databaseName,
            ]);

            // Step 5: Create central user linked to company
            $user = $company->users()->create([
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            // Step 6: Log user in
            auth()->login($user);

            // Step 7: Redirect to onboarding on subdomain
            $redirectUrl = "https://{$this->subdomain}." . config('app.domain') . '/onboarding';
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            \Log::error('Tenant registration failed: ' . $e->getMessage());
            $this->isCreating = false;
            session()->flash('error', 'Workspace creation failed. Please try again.');
        }
    }



public function validateSubdomain()
{
    if (in_array(strtolower($this->subdomain), $this->reservedSubdomains)) {
        throw ValidationException::withMessages([
            'subdomain' => 'This subdomain is reserved.'
        ]);
    }
}


    protected function provisionDatabase(string $dbName)
    {
        // Shared hosting: use cPanel API
        if (app()->environment('production')) {
            $response = Http::withBasicAuth(
                env('CPANEL_USERNAME'),
                env('CPANEL_API_TOKEN')
            )->get("https://" . env('CPANEL_HOST') . ":2083/execute/Mysql/create_database", [
                'name' => $dbName,
            ]);

            if (! $response->successful() || ! $response->json('status')) {
                throw new \Exception('Failed to create database via cPanel.');
            }
        } else {
            // Local: use Tenancy's built-in creator (assumes DB user has CREATE privs)
            app(TenantDatabaseManager::class)->createDatabase($dbName);
        }
    }

    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        $viewPath =  "qf::components.livewire.$UIFramework";
        $theView = "$viewPath.tenants.register-tenant"; 

        return view($theView);
            //->layout("$viewPath.layouts.app"); // 👈 important 
    }
}


/*
********* For additional security *******



Security & Rate Limiting
    1) Subdomain Validation Rules (already in Livewire):
    Regex: /^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/
    Length: 3–32 chars
    Reserved words: block www, api, admin, etc.
    Add to RegisterTenant.php:

    protected $reservedSubdomains = ['www', 'api', 'admin', 'mail', 'localhost', 'test'];

    public function validateSubdomain()
    {
        if (in_array(strtolower($this->subdomain), $this->reservedSubdomains)) {
            throw ValidationException::withMessages([
                'subdomain' => 'This subdomain is reserved.'
            ]);
        }
    }



    (2) Rate Limiting (in RouteServiceProvider)
    // app/Providers/RouteServiceProvider.php

    protected function configureRateLimiting()
    {
        RateLimiter::for('tenant_registration', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }

    // Then in routes:
    // routes/web.php
    Route::middleware('throttle:tenant_registration')->group(function () {
        Route::get('/register', [RegisterTenant::class, 'render']);
    });





*/