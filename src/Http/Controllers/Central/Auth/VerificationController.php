<?php
namespace QuickerFaster\LaravelUI\Http\Controllers\Central\Auth;

use App\Modules\System\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function show()
    {
        return view('auth.verify-email');
    }

    public function verify(string $token): RedirectResponse
    {
        $company = Company::where('email_verification_token', $token)
            ->where('email_verification_sent_at', '>', Carbon::now()->subHours(24))
            ->first();

        if (!$company) {
            return redirect()->route('register')->withErrors('Invalid or expired token.');
        }

        // Store company in session for configuration step
        session(['verified_company_id' => $company->id]);

        // Mark company as verified
        $company->update([
            'domain_verified' => true,
            'email_verification_token' => null,
            'email_verification_sent_at' => null,
        ]);

        // 👈 REDIRECT TO CONFIGURATION (NOT MODULE SELECTION)
        return redirect()->route('central.quick.configure');
    }
}