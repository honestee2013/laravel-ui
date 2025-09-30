<?php
namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Database;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class BaseStatusMigration extends Migration
{
    protected function defineStatusColumns(Blueprint $table)
    {
        $table->id();
        $table->string('name')->unique();
        $table->text('description')->nullable();
        $table->foreignId('category_id')->nullable(); // Assuming you might have a categories table
        $table->string('editable')->default("Yes");
        $table->timestamps(); // Optional, but often useful
    }
}