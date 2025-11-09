<?php


namespace QuickerFaster\LaravelUI\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use App\Modules\System\Models\Company;

class EnsureCompanyVerified
{
    // QuickerFaster\LaravelUI\Http\Middleware\Auth\EnsureCompanyVerified

    public function handle(Request $request, Closure $next)
    {
        // Allow access if session has verified company
        if (session()->has('verified_company_id')) {
            return $next($request);
        }

        // Optional: handle token in URL (e.g., if user refreshes verification link)
        $token = $request->query('token');
        if ($token) {
            $company = Company::where('email_verification_token', $token)
                ->where('email_verification_sent_at', '>', Carbon::now()->subHours(24))
                ->first();

            if ($company) {
                session(['verified_company_id' => $company->id]);
                return $next($request);
            }
        }

        // Not verified → redirect to register
        return redirect()->route('register');
    }
}