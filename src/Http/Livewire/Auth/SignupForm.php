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
        $this->validate();

        DB::transaction(function () {

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


            // Create user account
            $user = User::create([
                'name' => 'Admin', // We'll collect actual name later
                'email' => $this->billing_email,
                'password' => Hash::make($this->password),
                'company_id' => $company->id,
            ]);


            // 👈 ASSIGN ADMIN ROLE USING SPATIE PERMISSION
            $adminRole = Role::where('name', 'admin')->first();

            if ($adminRole) {
                $user->assignRole($adminRole);
            } else {
                // Fallback: create admin role if it doesn't exist
                $adminRole = Role::create([
                    'name' => 'admin',
                    'description' => 'Manage users, settings, and system operations',
                    'guard_name' => 'web',
                    'editable' => 'No',
                ]);
                $user->assignRole($adminRole);
            }



            // Generate and store verification token
            $token = sha1(random_bytes(40));
            $company->update(['email_verification_token' => $token, 'email_verification_sent_at' => Carbon::now()]);


\Log::info(url('verify/'.$token));


            // Store user ID in session for login after verification
            session(['user_password' => $user->password]);

            session(['pending_user_id' => $user->id]);

            // Send verification email
            ////\Mail::to($this->billing_email)->send(new \QuickerFaster\LaravelUI\Mail\VerifyCompanyEmail($company, $token));

        });
        
        session()->flash('message', 'Check your email to verify your account.');
        return redirect()->route('central.client.register');
        
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