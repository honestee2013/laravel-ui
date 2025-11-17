<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Add tenancy_db_name if it doesn't exist
            if (!Schema::hasColumn('tenants', 'tenancy_db_name')) {
                $table->string('tenancy_db_name')->nullable()->after('id');
            }

            // Ensure data column is JSON
            $table->json('data')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('tenancy_db_name');
        });
    }
};
