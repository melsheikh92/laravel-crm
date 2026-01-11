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
        Schema::create('extension_installations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('updated_at_version')->nullable();
            $table->enum('status', ['active', 'inactive', 'failed', 'updating', 'uninstalling'])->default('active');
            $table->boolean('auto_update_enabled')->default(false);
            $table->text('installation_notes')->nullable();
            $table->json('settings')->nullable();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('extension_id')->unsigned();
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');

            $table->integer('version_id')->unsigned();
            $table->foreign('version_id')->references('id')->on('extension_versions')->onDelete('cascade');

            $table->timestamps();

            // Add indexes for faster queries
            $table->index(['user_id', 'extension_id']);
            $table->index(['extension_id', 'status']);
            $table->index('status');
            $table->index('installed_at');

            // Ensure user can't install same extension twice (should update version instead)
            $table->unique(['user_id', 'extension_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_installations');
    }
};
