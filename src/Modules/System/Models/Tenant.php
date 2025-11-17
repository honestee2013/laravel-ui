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

    protected $fillable = ['id', 'data', 'tenancy_db_name'];
    protected $casts = ['data' => 'array'];

    /**
     * Override to prevent automatic database creation
     */
    public function getDatabaseName(): string
    {
        // Always use the database from available_databases pool
        $assignedDb = DB::connection('mysql')
            ->table('available_databases')
            ->where('tenant_id', $this->id)
            ->value('name');

        if ($assignedDb) {
            \Log::info('Using database from pool', [
                'tenant_id' => $this->id,
                'database' => $assignedDb
            ]);
            return $assignedDb;
        }

        // Fallback
        return $this->id . '_db';
    }

    /**
     * Override to skip database creation
     */
    protected function createDatabase(): void
    {
        $database = $this->getDatabaseName();

        \Log::info('Skipping automatic database creation', [
            'tenant_id' => $this->id,
            'database' => $database
        ]);

        // Just verify it exists
        $result = DB::connection('mysql')->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
            [$database]
        );

        if (empty($result)) {
            throw new \Exception("Database from pool does not exist: {$database}");
        }

        \Log::info('Database verified successfully', ['database' => $database]);
    }
}
