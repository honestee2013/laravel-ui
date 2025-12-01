<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Tenants;

use Livewire\Component;
use App\Modules\System\Models\Company;
;

class OnboardingForm extends Component
{
    public $billing_address_line_1, $billing_city, $billing_postal_code, $billing_country_code;

    public function mount()
    {
        $company = tenant();
        $this->billing_address_line_1 = $company->billing_address_line_1;
        $this->billing_city = $company->billing_city;
        $this->billing_postal_code = $company->billing_postal_code;
        $this->billing_country_code = $company->billing_country_code;
    }

    public function save()
    {
        tenant()->update([
            'billing_address_line_1' => $this->billing_address_line_1,
            'billing_city' => $this->billing_city,
            'billing_postal_code' => $this->billing_postal_code,
            'billing_country_code' => $this->billing_country_code,
        ]);

        return redirect('/dashboard'); // or next step
    }

    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $viewPath = "qf::components.livewire.$UIFramework";
        return view("$viewPath.tenants.onboarding-form")
            ;//->layout("$viewPath.layouts.app");


    }
}