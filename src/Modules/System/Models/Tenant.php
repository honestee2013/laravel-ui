<?php
namespace App\Modules\System\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $fillable = ['id', 'data'];

    /**
     * This is the method the package uses to get the database name
     * Override it completely to ignore the config prefix/suffix
     */
    public function getDatabaseName(): string
    {
        // Always use custom database name from data
        if (isset($this->data['database_name']) && !empty($this->data['database_name'])) {
            return $this->data['database_name'];
        }

        // Fallback to available_databases table
        $assignedDb = DB::connection('mysql') // Use central connection
            ->table('available_databases')
            ->where('tenant_id', $this->id)
            ->value('name');
            
        if ($assignedDb) {
            return $assignedDb;
        }

        // Final fallback - but this should rarely happen
        return 'tenant_' . $this->id . '_db';
    }

    /**
     * Some package versions use this method - override it too
     */
    public function getTenantDatabaseName(): string
    {
        return $this->getDatabaseName();
    }

    /**
     * Force the database configuration to use our custom name
     */
public function getDatabaseConfig(): array
{
    // Get the base tenant connection config
    $defaultConfig = config('database.connections.tenant', [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ]);

    return array_merge($defaultConfig, [
        'database' => $this->getDatabaseName(), // This sets the actual database name
    ]);
}
}