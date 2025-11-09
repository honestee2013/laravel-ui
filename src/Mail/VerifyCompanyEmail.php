<?php

namespace QuickerFaster\LaravelUI\Mail;

use App\Modules\System\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyCompanyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $token;

    public function __construct(Company $company, string $token)
    {
        $this->company = $company;
        $this->token = $token;
    }

// app/Mail/VerifyCompanyEmail.php

public function build()
{
    $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
    $theView = "qf::components.livewire.{$UIFramework}.emails.verify-company";

    $url = url("/verify/{$this->token}");

    return $this
        ->from(config('mail.from.address'), config('mail.from.name'))
        ->subject("Verify your {$this->company->name} workspace")
        ->view($theView)
        ->with([
            'company_name' => $this->company->name,
            'verification_url' => $url,
        ]);
}
}