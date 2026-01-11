<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique();
        });

        $tableName = DB::getTablePrefix().'persons';
        $driver = config('database.default');

        // Use different SQL syntax for different database drivers
        if ($driver === 'sqlite') {
            // SQLite syntax
            DB::statement("
                UPDATE {$tableName}
                SET unique_id =
                    CAST(user_id AS TEXT) || '|' ||
                    CAST(organization_id AS TEXT) || '|' ||
                    COALESCE(json_extract(emails, '$[0].value'), '') || '|' ||
                    COALESCE(json_extract(contact_numbers, '$[0].value'), '')
            ");
        } else {
            // MySQL/PostgreSQL syntax
            DB::statement("
                UPDATE {$tableName}
                SET unique_id = CONCAT(
                    user_id, '|',
                    organization_id, '|',
                    JSON_UNQUOTE(JSON_EXTRACT(emails, '$[0].value')), '|',
                    JSON_UNQUOTE(JSON_EXTRACT(contact_numbers, '$[0].value'))
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });
    }
};