<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('category');
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('inactive');
            $table->boolean('is_installed')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->unsignedInteger('installed_by')->nullable();
            $table->string('version')->nullable();
            $table->string('settings_url')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamps();

            $table->foreign('installed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('is_installed');
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};

