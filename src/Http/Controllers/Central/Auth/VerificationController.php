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
    \Log::info('=== VERIFICATION PROCESS START ===');
    
    // FORCE central database connection
    $previousConnection = config('database.default');
    config(['database.default' => 'mysql']);
    \DB::purge('mysql');
    \DB::reconnect('mysql');
    
    \Log::info('Database connection forced', [
        'previous_connection' => $previousConnection,
        'current_connection' => config('database.default'),
        'current_database' => \DB::connection()->getDatabaseName()
    ]);

    // Test: Count all companies with tokens
    $allCompaniesWithTokens = \DB::table('companies')
        ->whereNotNull('email_verification_token')
        ->get();
        
    \Log::info('All companies with tokens in forced connection', [
        'count' => $allCompaniesWithTokens->count(),
        'companies' => $allCompaniesWithTokens->map(function($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->billing_email,
                'token' => $company->email_verification_token
            ];
        })
    ]);

    // Test: Raw SQL query
    $rawResults = \DB::select(
        "SELECT id, name, billing_email, email_verification_token, email_verification_sent_at 
         FROM companies 
         WHERE email_verification_token = ?",
        [$token]
    );
    
    \Log::info('Raw SQL query results', [
        'token_used' => $token,
        'raw_results_count' => count($rawResults),
        'raw_results' => $rawResults
    ]);

    // Now try Eloquent query
    $company = Company::where('email_verification_token', $token)
        ->where('email_verification_sent_at', '>', Carbon::now()->subHours(24))
        ->first();

    \Log::info('Eloquent query result', [
        'company_found' => !is_null($company),
        'company_id' => $company ? $company->id : null,
        'company_name' => $company ? $company->name : null
    ]);

    if (!$company) {
        \Log::warning('=== VERIFICATION FAILED ===');
        
        // Restore previous connection
        config(['database.default' => $previousConnection]);
        \DB::purge($previousConnection);
        \DB::reconnect($previousConnection);
        
        return redirect()->route('central.client.register')->withErrors('Invalid or expired token.');
    }

    \Log::info('=== VERIFICATION SUCCESS ===');
    
    // Store company in session for configuration step
    session(['verified_company_id' => $company->id]);
    
    // Mark company as verified
    $company->update([
        'domain_verified' => true,
        'email_verification_token' => null,
        'email_verification_sent_at' => null,
    ]);

    \Log::info('Company verified and updated', [
        'company_id' => $company->id,
        'session_verified_company_id' => session('verified_company_id')
    ]);

    // Restore previous connection
    config(['database.default' => $previousConnection]);
    \DB::purge($previousConnection);
    \DB::reconnect($previousConnection);

    return redirect()->route('central.quick.configure');
}




}