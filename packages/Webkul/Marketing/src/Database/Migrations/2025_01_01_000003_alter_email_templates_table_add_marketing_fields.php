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
        Schema::table('email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('email_templates', 'type')) {
                $table->enum('type', ['system', 'custom'])->default('custom')->after('content');
            }
            if (!Schema::hasColumn('email_templates', 'variables')) {
                $table->json('variables')->nullable()->after('type');
            }
            if (!Schema::hasColumn('email_templates', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('variables');
            }
            if (!Schema::hasColumn('email_templates', 'user_id')) {
                $table->unsignedInteger('user_id')->nullable()->after('thumbnail');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('email_templates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('user_id');
            }

            // Add indexes if they don't exist
            if (!$this->hasIndex('email_templates', 'email_templates_type_index')) {
                $table->index('type');
            }
            if (!$this->hasIndex('email_templates', 'email_templates_is_active_index')) {
                $table->index('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['type', 'variables', 'thumbnail', 'user_id', 'is_active']);
        });
    }

    /**
     * Check if index exists.
     */
    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
        
        try {
            $indexes = $doctrineSchemaManager->listTableIndexes($table);
            return isset($indexes[$index]);
        } catch (\Exception $e) {
            return false;
        }
    }
};

