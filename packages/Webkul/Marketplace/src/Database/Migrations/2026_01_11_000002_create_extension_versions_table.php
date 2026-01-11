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
        Schema::create('extension_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('version');
            $table->text('changelog')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'archived'])->default('draft');
            $table->timestamp('release_date')->nullable();

            // Compatibility fields
            $table->string('laravel_version')->nullable();
            $table->string('crm_version')->nullable();
            $table->string('php_version')->nullable();
            $table->json('dependencies')->nullable();

            // Additional metadata
            $table->unsignedInteger('downloads_count')->default(0);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum')->nullable();

            $table->integer('extension_id')->unsigned();
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');

            $table->timestamps();

            // Add index for faster queries
            $table->index(['extension_id', 'status']);
            $table->index('release_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_versions');
    }
};
