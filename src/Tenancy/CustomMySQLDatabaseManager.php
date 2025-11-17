<?php

namespace QuickerFaster\LaravelUI\Tenancy;

use Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager;
use Illuminate\Support\Facades\DB;

class CustomMySQLDatabaseManager extends MySQLDatabaseManager
{
    public function createDatabase(string $name): bool
    {
        // Don't create database - we're managing this manually
        \Log::info('Skipping database creation - using pre-created database', ['database' => $name]);
        return true;
    }

    public function deleteDatabase(string $name): bool
    {
        // Don't delete database - we're managing this manually
        \Log::info('Skipping database deletion - manual management', ['database' => $name]);
        return true;
    }
}