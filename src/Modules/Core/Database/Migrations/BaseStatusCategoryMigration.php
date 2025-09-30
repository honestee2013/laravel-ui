<?php

namespace App\Modules\Core\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class BaseStatusCategoryMigration extends Migration
{
    protected function defineStatusCategoryColumns(Blueprint $table)
    {
        $table->id();
        $table->string('name')->unique();
        //$table->string('module_name')->nullable(); // Optional: to specify the module this category belongs to
        $table->text('description')->nullable();
        $table->string('editable')->default("Yes");
        $table->timestamps();
    }
}