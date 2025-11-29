<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Auth;

use App\Modules\System\Models\Company;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Modules\Admin\Models\Role;
use Illuminate\Support\Facades\DB;


class SignupForm extends Component
{

    public $company_name, $billing_email, $subdomain;
    public $password, $password_confirmation;
    public $auto_generated = true; // Track if subdomain was auto-generated


    protected $rules = [
        'company_name' => 'required|string|max:255',
        'billing_email' => 'required|email|max:255|unique:companies,billing_email',
        'subdomain' => [
            'required',
            'alpha_dash',
            'min:3',
            'max:63',
            'unique:companies,subdomain',
            'regex:/^[a-z][a-z0-9-]{1,61}[a-z0-9]$/',
        ],
        'password' => 'required|min:8|confirmed',
    ];


    public function mount()
    {
        // Optional: Generate initial subdomain if you want a placeholder
        // $this->generateSubdomain('mycompany');
    }


    // 👈 AUTO-GENERATE SUBDOMAIN WHEN COMPANY NAME CHANGES
    public function updatedCompanyName($value)
    {

        if ($this->auto_generated && !empty($value)) {
            $this->generateSubdomain($value);
        }
    }

    // 👈 RESET AUTO-GENERATED FLAG WHEN USER MANUALLY EDITS SUBDOMAIN
    public function updatedSubdomain()
    {
        $this->auto_generated = false;
    }

// In your SignupForm component
public function regenerateSubdomain()
{
    if (!empty($this->company_name)) {
        $this->generateSubdomain($this->company_name);
        $this->auto_generated = true;
    }
}



        protected function generateSubdomain($companyName)
    {
        // Convert to lowercase and remove special characters
        $base = Str::lower($companyName);
        $base = preg_replace('/[^a-z0-9]/', '-', $base); // Replace non-alphanumeric with hyphens
        $base = preg_replace('/-+/', '-', $base); // Replace multiple hyphens with single
        $base = trim($base, '-'); // Trim hyphens from ends
        
        // Ensure it meets length requirements
        if (strlen($base) < 3) {
            $base = $base . '-' . Str::random(3);
        } elseif (strlen($base) > 61) {
            $base = substr($base, 0, 61);
        }
        
        // Ensure it starts and ends with alphanumeric
        if (!preg_match('/^[a-z0-9]/', $base)) {
            $base = 'c-' . $base;
        }
        if (!preg_match('/[a-z0-9]$/', $base)) {
            $base = $base . '1';
        }
        
        $this->subdomain = $base;
    }







public function submit()
{


        // Ensure we're using central database
    config(['database.default' => 'mysql']);
    DB::purge('mysql');
    DB::reconnect('mysql');
    
    $this->validate();

    \Log::info('=== SIGNUP PROCESS START ===');
    \Log::info('Form data', [
        'company_name' => $this->company_name,
        'email' => $this->billing_email,
        'subdomain' => $this->subdomain
    ]);

    try {
        DB::beginTransaction();
        \Log::info('Transaction started');

        // Create company
        $company = Company::create([
            'name' => $this->company_name,
            'billing_email' => $this->billing_email,
            'subdomain' => $this->subdomain,
            'status' => 'Pending',
            'timezone' => 'UTC',
            'currency_code' => 'USD',
            'domain_verified' => false,
        ]);

        \Log::info('Company created', [
            'company_id' => $company->id,
            'company_name' => $company->name
        ]);

        // Create user account
        $user = User::create([
            'name' => 'Admin',
            'email' => $this->billing_email,
            'password' => Hash::make($this->password),
            'company_id' => $company->id,
        ]);

        \Log::info('User created', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->assignRole($adminRole);
            \Log::info('Admin role assigned');
        } else {
            $adminRole = Role::create([
                'name' => 'admin',
                'description' => 'Manage users, settings, and system operations',
                'guard_name' => 'web',
                'editable' => 'No',
            ]);
            $user->assignRole($adminRole);
            \Log::info('Admin role created and assigned');
        }

        // Generate and store verification token
        $token = sha1(random_bytes(40));
        $company->update([
            'email_verification_token' => $token, 
            'email_verification_sent_at' => Carbon::now()
        ]);

        \Log::info('Token generated and saved', [
            'token' => $token,
            'company_id' => $company->id
        ]);

        // Verify the token was actually saved
        $freshCompany = Company::find($company->id);
        \Log::info('Token verification - fresh company', [
            'token_in_db' => $freshCompany->email_verification_token,
            'token_matches' => $freshCompany->email_verification_token === $token
        ]);

        // Store in session
        session(['pending_company_id' => $company->id]);
        session(['pending_user_id' => $user->id]);

        \Log::info('Session set', [
            'pending_company_id' => session('pending_company_id'),
            'pending_user_id' => session('pending_user_id')
        ]);

        DB::commit();
        \Log::info('=== TRANSACTION COMMITTED SUCCESSFULLY ===');

        \Log::info('Verification URL: ' . url('verify/'.$token));

        return redirect()->route('central.client.register')
                ->with('message', 'Check your email to verify your account.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('=== TRANSACTION ROLLED BACK ===');
        \Log::error('Signup failed: ' . $e->getMessage());
        \Log::error('Exception trace: ' . $e->getTraceAsString());
        
        session()->flash('error', 'Registration failed. Please try again.');
        return back();
    }
}







    public function render()
    {


        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        $viewPath =  "qf::components.livewire.$UIFramework";
        $theView = "$viewPath.auth.signup-form"; // table, list, cards

        return view($theView)
            ->layout("$viewPath.layouts.app"); // 👈 important  

    }
}