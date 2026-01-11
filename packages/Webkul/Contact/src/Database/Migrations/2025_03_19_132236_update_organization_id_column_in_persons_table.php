<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support dropping foreign keys
        if (config('database.default') !== 'sqlite') {
            Schema::table('persons', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);

                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support dropping foreign keys
        if (config('database.default') !== 'sqlite') {
            Schema::table('persons', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);

                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
        }
    }
};