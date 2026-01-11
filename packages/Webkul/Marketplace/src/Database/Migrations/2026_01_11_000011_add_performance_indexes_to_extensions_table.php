<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extensions', function (Blueprint $table) {
            // Add indexes for common query patterns
            $table->index('status');
            $table->index('featured');
            $table->index('type');
            $table->index('category_id');
            $table->index('author_id');
            $table->index('created_at');
            $table->index(['status', 'featured']);
            $table->index(['status', 'type']);
            $table->index(['status', 'category_id']);
            $table->index(['status', 'downloads_count']);
            $table->index(['status', 'average_rating']);
            $table->index(['status', 'created_at']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);
            $table->dropIndex(['type']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['author_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'featured']);
            $table->dropIndex(['status', 'type']);
            $table->dropIndex(['status', 'category_id']);
            $table->dropIndex(['status', 'downloads_count']);
            $table->dropIndex(['status', 'average_rating']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['price']);
        });
    }
};
