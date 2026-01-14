<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Documentation Categories
        if (!Schema::hasTable('doc_categories')) {
            Schema::create('doc_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('doc_categories')->onDelete('cascade');
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->enum('visibility', ['public', 'internal', 'customer_portal'])->default('public');
                $table->timestamps();

                $table->index('slug');
                $table->index('visibility');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_categories');
    }
};
